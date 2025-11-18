<?php

namespace App\Notifications\Messages;

class FcmMessage
{
    protected array $notification = [];

    protected array $data = [];

    protected array $android = [];

    protected array $apns = [];

    protected array $webpush = [];

    /**
     * Set notification title
     */
    public function title(string $title): static
    {
        $this->notification['title'] = $title;

        return $this;
    }

    /**
     * Set notification body
     */
    public function body(string $body): static
    {
        $this->notification['body'] = $body;

        return $this;
    }

    /**
     * Set notification icon
     */
    public function icon(string $icon): static
    {
        $this->notification['icon'] = $icon;

        return $this;
    }

    /**
     * Set notification image
     */
    public function image(string $image): static
    {
        $this->notification['image'] = $image;

        return $this;
    }

    /**
     * Set custom data payload
     */
    public function data(array $data): static
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Set click action URL
     */
    public function clickAction(string $url): static
    {
        $this->data['click_action'] = $url;

        return $this;
    }

    /**
     * Set Android-specific configuration
     */
    public function android(array $config): static
    {
        $this->android = array_merge($this->android, $config);

        return $this;
    }

    /**
     * Set Android priority
     */
    public function priority(string $priority = 'high'): static
    {
        $this->android['priority'] = $priority;

        return $this;
    }

    /**
     * Set time to live in seconds
     */
    public function ttl(int $seconds): static
    {
        $this->android['ttl'] = $seconds.'s';

        return $this;
    }

    /**
     * Set APNS (iOS) configuration
     */
    public function apns(array $config): static
    {
        $this->apns = array_merge($this->apns, $config);

        return $this;
    }

    /**
     * Set iOS badge count
     */
    public function badge(int $count): static
    {
        $this->apns['payload']['aps']['badge'] = $count;

        return $this;
    }

    /**
     * Set iOS sound
     */
    public function sound(string $sound = 'default'): static
    {
        $this->apns['payload']['aps']['sound'] = $sound;

        return $this;
    }

    /**
     * Set web push configuration
     */
    public function webpush(array $config): static
    {
        $this->webpush = array_merge($this->webpush, $config);

        return $this;
    }

    /**
     * Set web push notification options
     */
    public function webNotification(array $options): static
    {
        $this->webpush['notification'] = array_merge($this->webpush['notification'] ?? [], $options);

        return $this;
    }

    /**
     * Set FCM options
     */
    public function fcmOptions(array $options): static
    {
        $this->webpush['fcm_options'] = array_merge($this->webpush['fcm_options'] ?? [], $options);

        return $this;
    }

    /**
     * Get notification payload
     */
    public function getNotification(): array
    {
        return $this->notification;
    }

    /**
     * Get data payload
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get Android configuration
     */
    public function getAndroid(): array
    {
        return $this->android;
    }

    /**
     * Get APNS configuration
     */
    public function getApns(): array
    {
        return $this->apns;
    }

    /**
     * Get web push configuration
     */
    public function getWebpush(): array
    {
        return $this->webpush;
    }

    /**
     * Create a new FCM message instance
     */
    public static function create(): static
    {
        return new static;
    }
}
