<?php

namespace Tests\Feature;

use App\Livewire\Admin\Auth\Login as AdminLogin;
use App\Livewire\Admin\Profile;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Tests\TestCase;

class SeparateGuardsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'status' => 'active', 'password' => 'password']);
    }

    private function loginAs(string $component, User $user): void
    {
        Livewire::test($component)->set('email', $user->email)->set('password', 'password')->call('login')->assertHasNoErrors();
    }

    public function test_both_guards_can_login_and_logout_independently(): void
    {
        $client = $this->user('user');
        $admin = $this->user('admin');
        $this->loginAs(Login::class, $client);
        $this->loginAs(AdminLogin::class, $admin);
        $this->assertAuthenticatedAs($client, 'web');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->withSession(['cart' => ['sentinel'], '_token' => 'retained-token']);
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest('admin');
        $this->assertAuthenticatedAs($client, 'web');
        $this->assertSame(['sentinel'], session('cart'));
        $this->assertSame('retained-token', session('_token'));
        $this->loginAs(AdminLogin::class, $admin);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest('web');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_wrong_role_cannot_login_or_replace_other_guard(): void
    {
        $client = $this->user('user');
        $admin = $this->user('admin');
        $this->loginAs(Login::class, $client);
        Livewire::test(AdminLogin::class)->set('email', $client->email)->set('password', 'password')->call('login')->assertHasErrors('email');
        $this->assertAuthenticatedAs($client, 'web');
        $this->assertGuest('admin');
        $this->loginAs(AdminLogin::class, $admin);
        Livewire::test(Login::class)->set('email', $admin->email)->set('password', 'password')->call('login')->assertHasErrors('email');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertAuthenticatedAs($client, 'web');
    }

    public function test_admin_routes_recheck_role_and_status(): void
    {
        $client = $this->user('user');
        $this->actingAs($client, 'web')->get('/admin/books')->assertRedirect('/admin/login');
        $this->actingAs($client, 'admin')->get('/admin/books')->assertForbidden();
        $admin = $this->user('admin');
        $this->actingAs($admin, 'admin')->get('/admin/books')->assertOk();
        $admin->update(['status' => 'inactive']);
        $this->get('/admin/books')->assertForbidden();
    }

    public function test_admin_profile_updates_only_admin_account(): void
    {
        $client = $this->user('user');
        $admin = $this->user('admin');
        $this->loginAs(Login::class, $client);
        $this->loginAs(AdminLogin::class, $admin);
        Livewire::test(Profile::class)->set('name', 'Updated Admin')->call('updateProfile')->assertHasNoErrors();
        $this->assertSame('Updated Admin', $admin->fresh()->name);
        $this->assertSame($client->name, $client->fresh()->name);
    }

    public function test_logout_cannot_be_triggered_by_get(): void
    {
        $this->get('/logout')->assertStatus(405);
        $this->get('/admin/logout')->assertStatus(405);
    }

    public function test_livewire_admin_mutations_are_blocked_after_role_revocation(): void
    {
        $admin = $this->user('admin');
        $response = $this->actingAs($admin, 'admin')->get('/admin/profile')->assertOk();
        preg_match('/wire:snapshot="([^"]+)"/', $response->getContent(), $matches);
        $snapshot = html_entity_decode($matches[1], ENT_QUOTES);
        $admin->update(['role' => 'user']);
        $this->postJson(app(HandleRequests::class)->getUpdateUri(), [
            'components' => [[
                'snapshot' => $snapshot,
                'updates' => ['name' => 'Unauthorized change'],
                'calls' => [['path' => '', 'method' => 'updateProfile', 'params' => []]],
            ]],
        ], ['X-Livewire' => 'true'])->assertForbidden();
        $this->assertNotSame('Unauthorized change', $admin->fresh()->name);
    }

    public function test_inactive_accounts_cannot_login(): void
    {
        foreach (['user' => Login::class, 'admin' => AdminLogin::class] as $role => $component) {
            $user = $this->user($role);
            $user->update(['status' => 'banned']);
            Livewire::test($component)->set('email', $user->email)->set('password', 'password')
                ->call('login')->assertHasErrors('email');
        }
        $this->assertGuest('web');
        $this->assertGuest('admin');
    }

    public function test_login_throttling_is_separate_per_guard(): void
    {
        $admin = $this->user('admin');
        $component = Livewire::test(Login::class)->set('email', $admin->email)->set('password', 'wrong-password');
        for ($i = 0; $i < 5; $i++) {
            $component->call('login')->assertHasErrors('email');
        }
        $component->call('login')->assertSee('Bạn thử đăng nhập quá nhiều lần');
        $this->loginAs(AdminLogin::class, $admin);
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_client_registration_preserves_admin_login(): void
    {
        $admin = $this->user('admin');
        $this->loginAs(AdminLogin::class, $admin);
        Livewire::test(Register::class)
            ->set('name', 'New Reader')->set('email', 'reader@example.test')
            ->set('password', 'new-password')->set('password_confirmation', 'new-password')
            ->call('register')->assertHasNoErrors()->assertRedirect('/');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertAuthenticatedAs(User::where('email', 'reader@example.test')->firstOrFail(), 'web');
    }
}
