<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

if (! function_exists('sanitizeText')) {
    /**
     * Sanitize text by removing dangerous HTML tags while preserving safe ones.
     *
     * @param  string|null  $text  The text to sanitize
     * @return string|null The sanitized text
     */
    function sanitizeText(?string $text): ?string
    {
        if (empty($text)) {
            return $text;
        }

        $allowedTags = '<a><b><i><u><strong><em><ul><ol><li><br><p><h1><h2><h3><h4><h5><h6><table><tr><td><th><tbody><thead><tfoot><caption><div><span><img><hr>';

        return strip_tags($text, $allowedTags);
    }
}

if (! function_exists('decryptOrAbort')) {
    /**
     * Decrypt a value or abort with 404 if decryption fails.
     *
     * @param  mixed  $encryptedValue  The encrypted value to decrypt
     * @return mixed The decrypted value or the original if empty
     */
    function decryptOrAbort(mixed $encryptedValue): mixed
    {
        if (empty($encryptedValue)) {
            return $encryptedValue;
        }

        try {
            return decrypt($encryptedValue);
        } catch (Exception $exception) {
            Log::warning('Decryption failed', [
                'message' => $exception->getMessage(),
            ]);
            abort(404);
        }
    }
}

if (! function_exists('customEncrypt')) {
    /**
     * Encrypt a value using AES-256-ECB encryption.
     *
     * @param  mixed  $value  The value to encrypt
     * @return string The encrypted value as a hex string
     */
    function customEncrypt(mixed $value): string
    {
        $encrypted = openssl_encrypt(
            (string) $value,
            'aes-256-ecb',
            config('app.key'),
            OPENSSL_RAW_DATA,
        );

        return bin2hex($encrypted);
    }
}

if (! function_exists('customDecrypt')) {
    /**
     * Decrypt a value that was encrypted with customEncrypt.
     *
     * @param  string  $encryptedValue  The encrypted value as a hex string
     * @param  bool  $abortOnFailure  Whether to abort with 404 on failure
     * @return string The decrypted value
     *
     * @throws Exception If decryption fails and abortOnFailure is false
     */
    function customDecrypt(string $encryptedValue, bool $abortOnFailure = false): string
    {
        try {
            $decrypted = openssl_decrypt(
                hex2bin($encryptedValue),
                'aes-256-ecb',
                config('app.key'),
                OPENSSL_RAW_DATA,
            );

            if ($decrypted === false) {
                throw new Exception('Decryption failed - invalid data or key');
            }

            return $decrypted;
        } catch (Exception $exception) {
            if ($abortOnFailure) {
                Log::warning('Custom decryption failed', [
                    'message' => $exception->getMessage(),
                ]);
                abort(404);
            }

            throw new Exception(
                sprintf('Failed to decrypt data: %s', $exception->getMessage())
            );
        }
    }
}

if (! function_exists('encryptModel')) {
    /**
     * Recursively encrypt specific fields in a model, collection, or array.
     *
     * @param  mixed  $data  The data to process (Model, Collection, array, or object)
     * @param  bool  $includeTimestamps  Whether to include timestamp and author fields
     * @return mixed The processed data with encrypted fields
     */
    function encryptModel(mixed $data, bool $includeTimestamps = false): mixed
    {
        if (is_null($data)) {
            return null;
        }

        if ($data instanceof Collection) {
            return $data->map(fn ($item) => encryptModel($item, $includeTimestamps))->all();
        }

        if ($data instanceof Model) {
            $data = $data->toArray();
        }

        if (is_array($data)) {
            return processArrayEncryption($data, $includeTimestamps);
        }

        if (is_object($data)) {
            $arrayData = json_decode(json_encode($data), true);

            return encryptModel($arrayData, $includeTimestamps);
        }

        return $data;
    }
}

if (! function_exists('processArrayEncryption')) {
    /**
     * Process array data and encrypt specific fields.
     *
     * @param  array  $data  The array data to process
     * @param  bool  $includeTimestamps  Whether to include timestamp fields
     * @return array The processed array with encrypted fields
     */
    function processArrayEncryption(array $data, bool $includeTimestamps): array
    {
        $timestampFields = ['created_by', 'updated_by', 'deleted_by', 'deleted_at', 'created_at', 'updated_at'];
        $encryptableFields = ['id', 'product_id', 'category_id', 'organization_id', 'created_by', 'updated_by', 'deleted_by', 'class_id', 'schedule_id'];

        $processedData = [];

        foreach ($data as $key => $value) {
            if (! $includeTimestamps && in_array($key, $timestampFields, true)) {
                continue;
            }

            if (in_array($key, $encryptableFields, true) && $value !== null) {
                $processedData[$key] = customEncrypt($value);
            } elseif (is_array($value) || is_object($value)) {
                $processedData[$key] = encryptModel($value, $includeTimestamps);
            } else {
                $processedData[$key] = $value;
            }
        }

        return $processedData;
    }
}
