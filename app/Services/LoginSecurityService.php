<?php

namespace App\Services;

use App\Models\LoginIpAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LoginSecurityService
{
    public function ensureLoginIsAllowed(?User $user, string $ipAddress): void
    {
        $now = now();
        $ipAttempt = LoginIpAttempt::firstOrCreate(['ip_address' => $ipAddress]);

        if ($this->releaseExpiredIpLock($ipAttempt, $now)) {
            AuditService::log('auth.login_ip_unlock_auto', null, ['ip_address' => $ipAddress]);
        }

        if ($ipAttempt->locked_until && $ipAttempt->locked_until->isFuture()) {
            throw ValidationException::withMessages([
                'login' => [$this->temporaryBlockMessage($ipAttempt->locked_until)],
            ]);
        }

        if ($user) {
            if ($this->releaseExpiredUserLock($user, $now)) {
                AuditService::log('auth.login_user_unlock_auto', $user, []);
            }

            if ($user->account_locked_until && $user->account_locked_until->isFuture()) {
                throw ValidationException::withMessages([
                    'login' => [$this->temporaryBlockMessage($user->account_locked_until)],
                ]);
            }
        }
    }

    public function recordFailedAttempt(?User $user, string $ipAddress, string $login): void
    {
        $maxAttempts = max(1, (int) config('login_security.max_failed_attempts', 3));
        $lockMinutes = max(1, (int) config('login_security.lock_minutes', 15));
        $now = now();

        $ipAttempt = LoginIpAttempt::firstOrCreate(['ip_address' => $ipAddress]);
        $ipAttempt->failed_attempts = min($maxAttempts, $ipAttempt->failed_attempts + 1);
        $ipAttempt->last_failed_at = $now;
        if ($ipAttempt->failed_attempts >= $maxAttempts) {
            $ipAttempt->locked_until = $now->copy()->addMinutes($lockMinutes);
            AuditService::log('auth.login_ip_locked', null, [
                'ip_address' => $ipAddress,
                'locked_until' => $ipAttempt->locked_until?->toDateTimeString(),
            ]);
        }
        $ipAttempt->save();

        if ($user) {
            $user->failed_login_attempts = min($maxAttempts, $user->failed_login_attempts + 1);
            $user->last_failed_login_at = $now;
            if ($user->failed_login_attempts >= $maxAttempts) {
                $user->account_locked_until = $now->copy()->addMinutes($lockMinutes);
                AuditService::log('auth.login_user_locked', $user, [
                    'locked_until' => $user->account_locked_until?->toDateTimeString(),
                ]);
            }
            $user->save();
        }

        AuditService::log('auth.login_failed', $user, [
            'login' => $login,
            'ip_address' => $ipAddress,
            'has_known_user' => (bool) $user,
        ]);
    }

    public function clearAttemptsOnSuccess(?User $user, string $ipAddress): void
    {
        $ipAttempt = LoginIpAttempt::where('ip_address', $ipAddress)->first();
        if ($ipAttempt && ($ipAttempt->failed_attempts > 0 || $ipAttempt->locked_until)) {
            $ipAttempt->forceFill([
                'failed_attempts' => 0,
                'last_failed_at' => null,
                'locked_until' => null,
            ])->save();

            AuditService::log('auth.login_ip_reset_after_success', null, ['ip_address' => $ipAddress]);
        }

        if ($user && ($user->failed_login_attempts > 0 || $user->account_locked_until)) {
            $user->forceFill([
                'failed_login_attempts' => 0,
                'last_failed_login_at' => null,
                'account_locked_until' => null,
            ])->save();

            AuditService::log('auth.login_user_reset_after_success', $user, []);
        }
    }

    public function unlockUser(User $user): void
    {
        $user->forceFill([
            'failed_login_attempts' => 0,
            'last_failed_login_at' => null,
            'account_locked_until' => null,
        ])->save();

        AuditService::log('auth.login_user_unlock_manual', $user, []);
    }

    private function temporaryBlockMessage(Carbon $lockedUntil): string
    {
        $secondsLeft = max(1, now()->diffInSeconds($lockedUntil));
        $minutes = (int) ceil($secondsLeft / 60);

        return "Login temporarily unavailable. Please try again in {$minutes} minute(s).";
    }

    private function releaseExpiredIpLock(LoginIpAttempt $ipAttempt, Carbon $now): bool
    {
        if ($ipAttempt->locked_until && $ipAttempt->locked_until->lte($now)) {
            $ipAttempt->forceFill([
                'failed_attempts' => 0,
                'last_failed_at' => null,
                'locked_until' => null,
            ])->save();

            return true;
        }

        return false;
    }

    private function releaseExpiredUserLock(User $user, Carbon $now): bool
    {
        if ($user->account_locked_until && $user->account_locked_until->lte($now)) {
            $user->forceFill([
                'failed_login_attempts' => 0,
                'last_failed_login_at' => null,
                'account_locked_until' => null,
            ])->save();

            return true;
        }

        return false;
    }
}
