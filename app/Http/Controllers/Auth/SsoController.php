<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class SsoController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $state = Str::random(64);
        $request->session()->put('sso_state', $state);

        $query = http_build_query([
            'client_id' => config('services.sso.client_id'),
            'redirect_uri' => config('services.sso.redirect_uri'),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        return redirect()->away(rtrim((string) config('services.sso.base_url'), '/').'/oauth/authorize?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->string('state')->isEmpty()
            || ! hash_equals((string) $request->session()->pull('sso_state'), $request->string('state')->toString())) {
            return $this->authenticationFailed('Sesi login SSO tidak valid. Silakan coba kembali.');
        }

        if ($request->string('code')->isEmpty()) {
            return $this->authenticationFailed('Autentikasi SSO dibatalkan atau gagal.');
        }

        try {
            $tokenResponse = Http::asForm()
                ->connectTimeout(3)
                ->timeout(10)
                ->post(rtrim((string) config('services.sso.base_url'), '/').'/oauth/token', [
                    'grant_type' => 'authorization_code',
                    'client_id' => config('services.sso.client_id'),
                    'client_secret' => config('services.sso.client_secret'),
                    'redirect_uri' => config('services.sso.redirect_uri'),
                    'code' => $request->string('code')->toString(),
                ]);

            if ($tokenResponse->failed() || ! is_string($tokenResponse->json('access_token'))) {
                return $this->authenticationFailed('Gagal mendapatkan token dari SSO.');
            }

            $identityResponse = Http::withToken($tokenResponse->json('access_token'))
                ->connectTimeout(3)
                ->timeout(10)
                ->get(rtrim((string) config('services.sso.base_url'), '/').'/api/user');

            if ($identityResponse->failed()) {
                return $this->authenticationFailed('Gagal mengambil identitas pengguna dari SSO.');
            }
        } catch (ConnectionException) {
            return $this->authenticationFailed('Server SSO tidak dapat dihubungi. Silakan coba kembali.');
        } catch (Throwable $exception) {
            report($exception);

            return $this->authenticationFailed('Terjadi kesalahan saat memproses login SSO.');
        }

        $identity = $identityResponse->json();
        $nik = is_array($identity) ? trim((string) ($identity['nik'] ?? '')) : '';

        if ($nik === '') {
            return $this->authenticationFailed('SSO tidak mengirimkan data NIK.');
        }

        $user = User::query()->firstOrCreate(
            ['nik' => $nik],
            [
                'name' => trim((string) ($identity['name'] ?? '')) ?: 'User '.$nik,
                'email' => trim((string) ($identity['email'] ?? '')) ?: Str::lower($nik).'@sso.local',
                'password' => null,
                'email_verified_at' => now(),
                'is_approved' => false,
            ],
        );

        if (! $user->is_approved) {
            return redirect()->route('login')->with('status', 'Akun Anda sudah tercatat dan menunggu aktivasi admin SITRESNO.');
        }

        $user->forceFill([
            'name' => trim((string) ($identity['name'] ?? '')) ?: $user->name,
            'last_login_at' => now(),
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function authenticationFailed(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['sso' => $message]);
    }
}
