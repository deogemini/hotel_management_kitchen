<?php

namespace Tests\Feature\Auth;

use App\Models\LoginIpAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginSecurityPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_and_ip_are_locked_after_three_failed_attempts(): void
    {
        config([
            'login_security.max_failed_attempts' => 3,
            'login_security.lock_minutes' => 15,
        ]);

        $user = User::factory()->create();

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->from('/login')->post('/login', [
                'login' => $user->email,
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('login');
        }

        $user->refresh();
        $this->assertEquals(3, $user->failed_login_attempts);
        $this->assertNotNull($user->account_locked_until);

        $ipAttempt = LoginIpAttempt::where('ip_address', '127.0.0.1')->first();
        $this->assertNotNull($ipAttempt);
        $this->assertEquals(3, $ipAttempt->failed_attempts);
        $this->assertNotNull($ipAttempt->locked_until);

        $this->from('/login')->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_successful_login_clears_user_and_ip_failed_attempts(): void
    {
        $user = User::factory()->create([
            'failed_login_attempts' => 2,
            'last_failed_login_at' => now(),
        ]);

        LoginIpAttempt::create([
            'ip_address' => '127.0.0.1',
            'failed_attempts' => 2,
            'last_failed_at' => now(),
        ]);

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $user->refresh();
        $this->assertEquals(0, $user->failed_login_attempts);
        $this->assertNull($user->account_locked_until);
        $this->assertNull($user->last_failed_login_at);

        $ipAttempt = LoginIpAttempt::where('ip_address', '127.0.0.1')->first();
        $this->assertNotNull($ipAttempt);
        $this->assertEquals(0, $ipAttempt->failed_attempts);
        $this->assertNull($ipAttempt->locked_until);
        $this->assertNull($ipAttempt->last_failed_at);
    }

    public function test_admin_can_manually_unlock_a_user_account(): void
    {
        Carbon::setTestNow('2026-04-14 12:00:00');

        $admin = User::factory()->create(['role' => 'admin']);
        $lockedUser = User::factory()->create([
            'failed_login_attempts' => 3,
            'last_failed_login_at' => now(),
            'account_locked_until' => now()->addMinutes(15),
        ]);

        $this->actingAs($admin)->post(route('users.unlock-login-lock', $lockedUser))
            ->assertRedirect(route('users.index'));

        $lockedUser->refresh();
        $this->assertEquals(0, $lockedUser->failed_login_attempts);
        $this->assertNull($lockedUser->last_failed_login_at);
        $this->assertNull($lockedUser->account_locked_until);

        Carbon::setTestNow();
    }
}
