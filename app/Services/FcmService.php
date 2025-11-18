<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected string $credentialsPath;

    protected string $apiUrl;

    protected string $tokenUri;

    protected string $scope;

    protected ?array $credentials = null;

    protected ?string $accessToken = null;

    protected ?int $tokenExpiresAt = null;

    public function __construct()
    {
        $this->credentialsPath = config('fcm.credentials');
        $this->apiUrl = config('fcm.api_url');
        $this->tokenUri = config('fcm.token_uri');
        $this->scope = config('fcm.scope');
    }

    /**
     * Send FCM notification to single token
     */
    public function sendToToken(string $token, array $notification, array $data = []): bool
    {
        return $this->send([
            'token' => $token,
            'notification' => $notification,
            'data' => $data,
        ]);
    }

    /**
     * Send FCM notification to multiple tokens
     */
    public function sendToTokens(array $tokens, array $notification, array $data = []): array
    {
        $results = [];

        foreach ($tokens as $token) {
            $results[$token] = $this->sendToToken($token, $notification, $data);
        }

        return $results;
    }

    /**
     * Send FCM message
     */
    public function send(array $message): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            // dd($accessToken);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'message' => $this->buildMessage($message),
            ]);

            if ($response->successful()) {
                $this->log('FCM notification sent successfully', [
                    'token' => $message['token'] ?? null,
                    'response' => $response->json(),
                ]);

                return true;
            }

            $this->log('FCM notification failed', [
                'token' => $message['token'] ?? null,
                'status' => $response->status(),
                'response' => $response->json(),
            ], 'error');

            return false;
        } catch (Exception $e) {
            $this->log('FCM notification exception', [
                'token' => $message['token'] ?? null,
                'error' => $e->getMessage(),
            ], 'error');

            return false;
        }
    }

    /**
     * Build FCM message payload
     */
    protected function buildMessage(array $message): array
    {
        $payload = [
            'token' => $message['token'],
        ];

        // Add notification if provided
        if (! empty($message['notification'])) {
            $payload['notification'] = $message['notification'];
        }

        // Add data if provided (convert all values to strings as required by FCM)
        if (! empty($message['data'])) {
            $payload['data'] = array_map(fn ($value) => (string) $value, $message['data']);
        }

        // Add Android configuration
        if (isset($message['android'])) {
            $payload['android'] = $message['android'];
        } else {
            $payload['android'] = [
                'priority' => config('fcm.notification.priority', 'high'),
                'ttl' => config('fcm.notification.ttl', 3600).'s',
            ];
        }

        // Add APNS (iOS) configuration
        if (isset($message['apns'])) {
            $payload['apns'] = $message['apns'];
        }

        // Add web push configuration
        if (isset($message['webpush'])) {
            $payload['webpush'] = $message['webpush'];
        }

        return $payload;
    }

    /**
     * Get OAuth2 access token
     */
    protected function getAccessToken(): string
    {
        // Return cached token if still valid
        if ($this->accessToken && $this->tokenExpiresAt && time() < $this->tokenExpiresAt) {
            return $this->accessToken;
        }

        // Load credentials
        $credentials = $this->getCredentials();

        // Create JWT
        $jwt = $this->createJwt($credentials);

        // Exchange JWT for access token
        $response = Http::asForm()->post($this->tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (! $response->successful()) {
            throw new Exception('Failed to get FCM access token: '.$response->body());
        }

        $data = $response->json();
        $this->accessToken = $data['access_token'];
        $this->tokenExpiresAt = time() + ($data['expires_in'] ?? 3600) - 60; // Subtract 60s for safety margin

        return $this->accessToken;
    }

    /**
     * Create JWT for service account authentication
     */
    protected function createJwt(array $credentials): string
    {
        $now = time();
        $expiration = $now + 3600;

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $payload = [
            'iss' => $credentials['client_email'],
            'sub' => $credentials['client_email'],
            'aud' => $this->tokenUri,
            'iat' => $now,
            'exp' => $expiration,
            'scope' => $this->scope,
        ];

        $segments = [];
        $segments[] = $this->base64UrlEncode(json_encode($header));
        $segments[] = $this->base64UrlEncode(json_encode($payload));

        $signingInput = implode('.', $segments);

        $signature = '';
        $privateKey = openssl_pkey_get_private($credentials['private_key']);

        if (! $privateKey) {
            throw new Exception('Invalid private key in credentials');
        }

        openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    /**
     * Base64 URL encode
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Load Firebase credentials from JSON file
     */
    protected function getCredentials(): array
    {
        if ($this->credentials) {
            return $this->credentials;
        }

        if (! file_exists($this->credentialsPath)) {
            throw new Exception('Firebase credentials file not found: '.$this->credentialsPath);
        }

        $this->credentials = json_decode(file_get_contents($this->credentialsPath), true);

        if (! $this->credentials || ! isset($this->credentials['private_key'])) {
            throw new Exception('Invalid Firebase credentials file');
        }

        return $this->credentials;
    }

    /**
     * Log FCM activity
     */
    protected function log(string $message, array $context = [], string $level = 'info'): void
    {
        if (! config('fcm.logging.enabled')) {
            return;
        }

        $channel = config('fcm.logging.channel', 'daily');

        Log::channel($channel)->$level($message, $context);
    }
}
