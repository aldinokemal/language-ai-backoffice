<?php

use App\Enums\StorageSource;
use App\Models\DB1\SysOrganization;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

if (!function_exists('logError')) {
    function logError(Exception $error): void
    {
        $params = collect(request()->all())->filter(function ($value, $key) {
            return !preg_match('/password|token/i', $key);
        })->toArray();

        Log::error($error->getMessage(), [
            'file'  => $error->getFile(),
            'line'  => $error->getLine(),
            'trace' => $error->getTraceAsString(),
            'code'  => $error->getCode(),
            'url'   => request()->fullUrl(),
            'ip'    => request()->ip(),
            'param' => $params,
        ]);
    }
}


if (!function_exists('mapOperatorNumberToSql')) {
    function mapOperatorNumberToSql($operator): string
    {
        return match ($operator) {
            'gte'          => '>=',
            'lte'          => '<=',
            'eq'           => '=',
            'neq'          => '!=',
            'contains'     => 'ILIKE',
            'not_contains' => 'NOT ILIKE',
            'gt'           => '>',
            'lt'           => '<',
            default        => $operator,
        };
    }
}

if (!function_exists('moneyFormat')) {
    function moneyFormat($amount): string
    {
        return number_format($amount, 2, ',', '.');
    }
}

if (!function_exists('compressImage')) {
    function compressImage(UploadedFile $uploadedFile): string
    {
        $manager = new ImageManager(new Driver());

        $image = $manager->read($uploadedFile);
        $image->scale(300);
        $filename = 'temporary-' . Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        $path     = Storage::disk('local')->path($filename);
        $image->save($path);

        return $path;
    }
}

if (!function_exists('goBack')) {
    function goBack($url): string
    {
        return url()->previous() !== url()->current() ? url()->previous() : url($url);
    }
}

if (!function_exists('isChildOpen')) {
    function isChildOpen($children): bool
    {
        if (empty($children)) {
            return false;
        }

        foreach ($children as $child) {
            $childUrl = trim($child->url ?? '', '/');
            if (request()->is($childUrl) || request()->is($childUrl.'/*')) {
                return true;
            }
            if (isChildOpen($child->children ?? [])) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('getUserImage')) {
    function getUserImage($user): string
    {
        if (!$user) {
            return asset('assets/media/avatars/blank.png');
        }

        if (!empty($user->picture)) {
            // Check if it's a full URL
            if (filter_var($user->picture, FILTER_VALIDATE_URL)) {
                return $user->picture;
            }

            // Check storage source
            if ($user->picture_storage === 'public' || empty($user->picture_storage)) {
                return asset('storage/' . $user->picture);
            } elseif ($user->picture_storage === StorageSource::S3->value) {
                return Storage::disk('s3')->temporaryUrl($user->picture, now()->addMinutes(5));
            }

            // Default to public path
            return asset($user->picture);
        }

        return asset('assets/media/avatars/blank.png');
    }
}

if (!function_exists('getOrganizationLogo')) {
    function getOrganizationLogo(?SysOrganization $organization = null): string
    {
        if (!$organization) {
            return asset('assets/media/avatars/blank.png');
        }

        if (!empty($organization->logo_path)) {
            // Check if it's a full URL
            if (filter_var($organization->logo_path, FILTER_VALIDATE_URL)) {
                return $organization->logo_path;
            }

            // Check storage source
            if ($organization->logo_storage === 'public' || empty($organization->logo_storage)) {
                return asset('storage/' . $organization->logo_path);
            } elseif ($organization->logo_storage === StorageSource::S3->value) {
                return Storage::disk('s3')->temporaryUrl($organization->logo_path, now()->addMinutes(5));
            }

            // Default to public path
            return asset($organization->logo_path);
        }

        return asset('assets/media/avatars/blank.png');
    }
}

if (!function_exists('getExtensionFromBase64')) {
    function getExtensionFromBase64($base64String): string
    {
        $data = explode(',', $base64String);
        $header = $data[0] ?? '';

        if (strpos($header, 'image/jpeg') !== false || strpos($header, 'image/jpg') !== false) {
            return 'jpg';
        } elseif (strpos($header, 'image/png') !== false) {
            return 'png';
        } elseif (strpos($header, 'image/gif') !== false) {
            return 'gif';
        } elseif (strpos($header, 'image/webp') !== false) {
            return 'webp';
        }

        return 'jpg'; // default
    }
}
