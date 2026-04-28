<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\LoginSecurityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureBasicRateLimit();

        $login = trim((string) $this->string('login'));
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        if ($field === 'email') {
            $login = Str::lower($login);
        }
        $ipAddress = (string) $this->ip();
        $user = User::where($field, $login)->first();
        $security = app(LoginSecurityService::class);

        $security->ensureLoginIsAllowed($user, $ipAddress);

        if (! Auth::attempt([$field => $login, 'password' => $this->string('password')], $this->boolean('remember'))) {
            $security->recordFailedAttempt($user, $ipAddress, $login);

            throw ValidationException::withMessages([
                'login' => ['Invalid credentials or login temporarily unavailable.'],
            ]);
        }

        $security->clearAttemptsOnSuccess(Auth::user(), $ipAddress);
        RateLimiter::clear($this->basicRateLimitKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureBasicRateLimit(): void
    {
        $maxRequests = max(1, (int) config('login_security.max_requests_per_minute', 20));
        $decaySeconds = max(1, (int) config('login_security.rate_limit_decay_seconds', 60));
        $rateKey = $this->basicRateLimitKey();

        if (! RateLimiter::tooManyAttempts($rateKey, $maxRequests)) {
            RateLimiter::hit($rateKey, $decaySeconds);
            return;
        }

        $seconds = RateLimiter::availableIn($rateKey);

        throw ValidationException::withMessages([
            'login' => ["Too many login requests. Try again in {$seconds} second(s)."],
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function basicRateLimitKey(): string
    {
        return Str::transliterate('login-rate-limit|'.$this->ip());
    }
}
