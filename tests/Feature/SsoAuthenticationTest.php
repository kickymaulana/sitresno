<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class SsoAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.sso', [
            'base_url' => 'http://sso.test',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'redirect_uri' => 'http://sitresno.test/auth/sso/callback',
        ]);
    }

    public function test_guest_is_redirected_to_sso_with_state(): void
    {
        $response = $this->get(route('sso.redirect'));

        $response->assertRedirectContains('http://sso.test/oauth/authorize?');
        $response->assertSessionHas('sso_state');

        $query = [];
        parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);

        $this->assertSame('client-id', $query['client_id']);
        $this->assertSame('code', $query['response_type']);
        $this->assertSame('http://sitresno.test/auth/sso/callback', $query['redirect_uri']);
        $this->assertNotEmpty($query['state']);
    }

    public function test_callback_rejects_an_invalid_state_without_calling_sso(): void
    {
        Http::fake();

        $this->withSession(['sso_state' => 'expected'])
            ->get(route('sso.callback', ['code' => 'code', 'state' => 'invalid']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('sso');

        Http::assertNothingSent();
    }

    public function test_new_sso_identity_is_provisioned_as_pending(): void
    {
        Http::fake([
            'http://sso.test/oauth/token' => Http::response(['access_token' => 'token']),
            'http://sso.test/api/user' => Http::response([
                'nik' => 'D123456',
                'name' => 'Pending User',
                'email' => 'pending@example.com',
            ]),
        ]);

        $state = Str::random();
        $this->withSession(['sso_state' => $state])
            ->get(route('sso.callback', ['code' => 'code', 'state' => $state]))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'nik' => 'D123456',
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'is_approved' => false,
        ]);
    }

    public function test_approved_identity_is_logged_in_and_name_is_synchronized(): void
    {
        $user = User::factory()->create([
            'nik' => 'D123456',
            'name' => 'Old Name',
            'is_approved' => true,
        ]);

        Http::fake([
            'http://sso.test/oauth/token' => Http::response(['access_token' => 'token']),
            'http://sso.test/api/user' => Http::response([
                'nik' => 'D123456',
                'name' => 'Updated Name',
                'email' => 'new-address@example.com',
            ]),
        ]);

        $state = Str::random();
        $this->withSession(['sso_state' => $state])
            ->get(route('sso.callback', ['code' => 'code', 'state' => $state]))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('Updated Name', $user->refresh()->name);
        $this->assertNotNull($user->last_login_at);
    }

    public function test_upstream_token_failure_returns_to_login(): void
    {
        Http::fake([
            'http://sso.test/oauth/token' => Http::response(['message' => 'invalid'], 401),
        ]);

        $state = Str::random();
        $this->withSession(['sso_state' => $state])
            ->get(route('sso.callback', ['code' => 'code', 'state' => $state]))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('sso');

        $this->assertGuest();
    }

    public function test_logout_only_ends_the_local_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_revoked_user_is_logged_out_on_the_next_request(): void
    {
        $user = User::factory()->create(['is_approved' => false]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('sso');

        $this->assertGuest();
    }
}
