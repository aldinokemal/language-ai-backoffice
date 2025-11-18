<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DB1\SysUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password as PasswordRules;

class ForgotPasswordController extends Controller
{
    private const MAX_ATTEMPTS = 3;

    /**
     * Display the forgot password form.
     */
    public function forgotPassword()
    {
        return view('auth::forgot-password');
    }

    /**
     * Handle forgot password form submission.
     */
    public function forgotPasswordPost(Request $request)
    {
        $key = 'password.reset.' . $request->ip();

        // Check if too many attempts
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => sprintf('Terlalu banyak percobaan reset kata sandi. Silakan coba lagi dalam %s menit.', ceil($seconds / 60)),
            ]);
        }

        // Validate email
        $input = $request->validate([
            'email' => 'required|email:rfc,dns',
        ]);

        $email = strtolower($input['email']);
        $user = SysUser::where('email', $email)->first();

        // Always hit rate limiter for security
        RateLimiter::hit($key);

        // Log all password reset attempts for security monitoring
        Log::info('Password reset requested', [
            'email' => $email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_exists' => $user ? true : false,
            'user_status' => $user ? [
                'banned' => $user->banned_at ? true : false,
                'verified' => $user->email_verified_at ? true : false,
                'deleted' => $user->deleted_at ? true : false,
            ] : null,
        ]);

        // Only send reset email if user exists, is active, verified, and not banned/deleted
        if ($user && 
            !$user->banned_at && 
            $user->email_verified_at && 
            !$user->deleted_at) {
            
            // Create token and send notification
            $token = Password::broker()->createToken($user);
            $user->sendPasswordResetNotification($token);
            
            Log::info('Password reset email sent', [
                'email' => $email,
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);
        } else {
            // Log failed attempts with reasons for security analysis
            $reasons = [];
            if (!$user) $reasons[] = 'user_not_found';
            if ($user && $user->banned_at) $reasons[] = 'user_banned';
            if ($user && !$user->email_verified_at) $reasons[] = 'email_not_verified';
            if ($user && $user->deleted_at) $reasons[] = 'user_deleted';
            
            Log::warning('Password reset blocked', [
                'email' => $email,
                'reasons' => $reasons,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Always return same success message to prevent user enumeration
        return redirect()->route('forgot-password-success')->with([
            'email'   => $email,
            'message' => 'Jika email terdaftar, tautan reset kata sandi telah dikirim.',
        ]);
    }

    /**
     * Show the password reset success page.
     */
    public function forgotPasswordSuccess()
    {
        if (!session('email')) {
            return redirect()->route('forgot-password');
        }

        session()->keep(['email']);

        return view('auth::forgot-password-success');
    }

    /**
     * Show the reset password form.
     */
    public function resetPassword(Request $request, $token = null)
    {
        $email = $request->query('email');

        if (!$email || !$token) {
            return redirect()->route('login')->withErrors([
                'message' => 'Tautan reset kata sandi tidak valid.',
            ]);
        }

        return view('auth::reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Handle password reset form submission.
     */
    public function resetPasswordPost(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email:rfc,dns|exists:sys_users,email',
            'password' => [
                'required',
                'confirmed',
                PasswordRules::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Kata sandi Anda berhasil direset!')
            : back()->withErrors(['email' => 'Gagal mereset kata sandi. Silakan coba lagi.']);
    }

    /**
     * Resend password reset email.
     */
    public function resendEmail(Request $request)
    {
        $email = session('email');

        session()->keep(['email']);

        if (!$email) {
            return redirect()->route('forgot-password');
        }

        $key = 'password.reset.' . $request->ip();

        // Check if too many attempts
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'message' => sprintf('Terlalu banyak percobaan kirim ulang email. Silakan coba lagi dalam %s menit.', ceil($seconds / 60)),
            ]);
        }

        $user = SysUser::where('email', $email)->first();

        if ($user) {
            $token = Password::broker()->createToken($user);
            $user->sendPasswordResetNotification($token);
            RateLimiter::hit($key);

            return back()->with('message', 'Tautan reset kata sandi telah dikirim ulang ke email Anda.');
        }

        return redirect()->route('forgot-password');
    }
}
