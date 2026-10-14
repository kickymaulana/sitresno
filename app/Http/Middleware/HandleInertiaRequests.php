<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => fn (): array => [
                'user' => $request->user() instanceof User ? [
                    'id' => $request->user()->id,
                    'nik' => $request->user()->nik,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'roles' => $request->user()->getRoleNames()->values(),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name')->values(),
                ] : null,
            ],
            'flash' => fn (): array => [
                'status' => $request->session()->get('status'),
            ],
        ];
    }
}
