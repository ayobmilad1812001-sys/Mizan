<?php

namespace App\Http\Requests;

use App\Enums\UserStatus;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * A wrong password counts against the rate limiter; an inactive account does not
     * (the credentials were correct, so it is not a brute-force signal).
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'بيانات الدخول غير صحيحة.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => match ($user->status) {
                    UserStatus::Pending => 'الحساب بانتظار قبول الدعوة وتفعيله.',
                    UserStatus::Disabled => 'تم إيقاف هذا الحساب. تواصل مع مدير النظام.',
                    default => 'لا يمكن تسجيل الدخول بهذا الحساب.',
                },
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "محاولات كثيرة جداً. أعد المحاولة بعد {$seconds} ثانية.",
        ]);
    }

    private function throttleKey(): string
    {
        return Str::lower($this->input('email')).'|'.$this->ip();
    }
}
