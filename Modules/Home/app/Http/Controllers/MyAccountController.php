<?php

namespace Modules\Home\Http\Controllers;

use App\Classes\Breadcrumbs;
use App\Enums\StorageSource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Modules\Home\Notifications\OtpUpdateEmail;

class MyAccountController extends Controller
{
    private string $url = '/my-account';

    private function defaultParser(): array
    {
        return [
            'url' => $this->url,
        ];
    }

    public function index()
    {
        $breadcrumbs = [
            new Breadcrumbs('My Account', route('my-account.profile')),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
            'user' => auth()->user(),
            'organization' => session('org')->organization,
        ]);

        return view('home::my_account.index')->with($parser);
    }

    public function updateBasicSettings(Request $request)
    {
        $user = auth()->user();

        $input = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
            'avatar' => 'nullable|image|mimes:png,jpg,jpeg|max:5120', // 5MB max
            'avatar_remove' => 'nullable|boolean',
        ]);

        // Update user data
        $user->update([
            'name' => sanitizeText($input['name']),
            'phone' => sanitizeText($input['phone']),
        ]);

        // Handle avatar removal
        if ($request->has('avatar_remove') && $input['avatar_remove']) {
            $this->deleteUserAvatar($user);

            $user->update([
                'picture' => null,
                'picture_storage' => null,
            ]);
        }
        // Handle avatar upload
        elseif ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Generate file path
            $extension = $file->getClientOriginalExtension();
            $filePath = sprintf(config('app.image_profile_path'), $user->id, $extension);

            // Create temporary file for processing
            $tempPath = $file->getPathname();

            // Process image with Intervention
            $image = ImageManager::imagick()->read($tempPath);
            $image->resize(150, 150);

            // Save processed image to temporary location
            $processedTempPath = storage_path('app/temp/'.Str::uuid().'.'.$extension);

            // Ensure temp directory exists
            if (! file_exists(dirname($processedTempPath))) {
                mkdir(dirname($processedTempPath), 0755, true);
            }

            $image->save($processedTempPath);

            // Remove old avatar before uploading new one
            $this->deleteUserAvatar($user);

            // Upload to storage
            Storage::disk(StorageSource::S3->value)->put($filePath, file_get_contents($processedTempPath));

            // Clean up temporary file
            unlink($processedTempPath);

            $user->update([
                'picture' => $filePath,
                'picture_storage' => StorageSource::S3->value,
            ]);
        }

        $this->updateCache();

        return responseJSON('Settings updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $input = $request->validate([
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        // Check if current password matches
        if (! Hash::check($input['current_password'], $user->password)) {
            return responseJSON('Current password is incorrect', 422);
        }

        $user->password = Hash::make($input['new_password']);
        $user->save();

        return responseJSON('Password updated successfully');
    }

    public function updateAccountID(Request $request)
    {
        $user = auth()->user();

        // Basic validation
        $input = $request->validate([
            'email' => 'required|email:rfc,dns|unique:sys_users,email,'.$user->id,
            'username' => 'required|alpha_num|unique:sys_users,username,'.$user->id,
            'otp' => [
                'nullable',
                'string',
                'size:6',
                function ($attribute, $value, $fail) use ($request, $user) {
                    // Only validate OTP if email is being changed
                    if ($request->email !== $user->email) {
                        if (empty($value)) {
                            $fail('OTP is required when changing email address');

                            return;
                        }

                        // Get stored OTP data from cache
                        $otpData = cache()->get(sprintf(config('cache.email_otp.cacheKey'), $user->id));

                        if (! $otpData) {
                            $fail('OTP has expired');

                            return;
                        }

                        if ($otpData['otp'] !== $value) {
                            $fail('Invalid OTP code');

                            return;
                        }

                        if ($otpData['email'] !== $request->email) {
                            $fail('Email address mismatch');

                            return;
                        }
                    }
                },
            ],
        ]);

        // Update user data
        $user->update([
            'email' => sanitizeText(Str::of($input['email'])->lower()),
            'username' => sanitizeText(Str::of($input['username'])->lower()),
        ]);

        // Clear OTP from cache if email was changed
        if ($input['email'] !== $user->getOriginal('email')) {
            cache()->forget(sprintf(config('cache.email_otp.cacheKey'), $user->id));
        }

        return responseJSON('Account ID updated successfully');
    }

    public function sendEmailOTP(Request $request)
    {
        $input = $request->validate([
            'email' => 'required|email:rfc,dns|unique:sys_users,email,'.auth()->id(),
        ]);

        $user = auth()->user();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in cache with 5 minutes expiry
        cache()->put(sprintf(config('cache.email_otp.cacheKey'), $user->id), [
            'otp' => $otp,
            'email' => $input['email'],
        ], config('cache.email_otp.ttl'));

        // Send OTP email
        try {
            $user->notify(new OtpUpdateEmail($otp, $input['email']));

            return responseJSON('OTP sent successfully to your email');
        } catch (\Exception $e) {
            logError($e);

            return responseJSON('Failed to send OTP email', 500);
        }
    }

    private function updateCache()
    {
        $cacheKey = sprintf(config('cache.img_profile.cacheKey'), auth()->id());
        cache()->forget($cacheKey);
    }

    private function deleteUserAvatar($user)
    {
        if ($user->picture && ! str_contains($user->picture, 'default.png')) {
            Storage::disk($user->picture_storage ?? 'local')->delete($user->picture);
        }
    }
}
