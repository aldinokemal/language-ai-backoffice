<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Auth\Traits\SessionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use SessionTrait;
    private string $module = __CLASS__;

    private string $url = 'auth/login';

    private const MAX_ATTEMPTS = 5;

    private function defaultParser(): array
    {
        return [
            'url'    => $this->url,
            'module' => $this->module,
        ];
    }

    public function login()
    {
        return view('auth::login', $this->defaultParser());
    }

    public function loginPost(Request $request)
    {
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            
            // Log lockout event for security monitoring
            Log::warning('Login rate limit exceeded - IP locked out', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'lockout_duration' => $seconds,
                'max_attempts' => self::MAX_ATTEMPTS,
            ]);
            
            throw ValidationException::withMessages([
                'message' => [
                    sprintf('Terlalu banyak percobaan login. Silakan coba lagi dalam %s menit.', ceil($seconds / 60)),
                ],
            ]);
        }

        $credentials = $request->validate([
            'email'    => 'required|email|max:255',
            'password' => 'required|string',
        ]);

        $attemptData = [
            'email'    => strtolower($credentials['email']),
            'password' => $credentials['password'],
        ];

        $remember = $request->boolean('check'); // Based on the form field name 'check'

        if (!Auth::attempt($attemptData, $remember)) {
            RateLimiter::hit($key);
            
            // Log failed login attempts
            Log::warning('Failed login attempt', [
                'email' => $attemptData['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);
            
            return back()->withErrors([
                'message' => 'Email atau password salah.',
            ])->withInput($request->except('password'));
        }

        $user = Auth::user();
        if ($user->banned_at) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // Log banned user login attempt
            Log::warning('Banned user login attempt', [
                'user_id' => $user->id,
                'email' => $user->email,
                'banned_at' => $user->banned_at,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return back()->withErrors([
                'message' => 'Akun Anda telah diblokir.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $this->setSession();

        // Store user ID in session for cached auth
        session(['user_id' => $user->id]);

        // Log successful login
        Log::info('Successful login', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'remember' => $remember,
            'timestamp' => now(),
        ]);

        return redirect()->intended('dashboard')
            ->header('X-Frame-Options', 'DENY')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $locale = session('locale');

        // Log logout activity
        Log::info('User logout', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        // Clear cached user data
        Cache::forget("auth_user_{$user->id}");

        // Flush all session data first
        $request->session()->flush();
        
        // Then detach roles and logout
        $user->roles()->detach();
        Auth::logout();
        
        // Regenerate session ID for security
        $request->session()->regenerateToken();

        // Preserve locale setting
        session(['locale' => $locale]);
        return redirect()->route('login');
    }
}
