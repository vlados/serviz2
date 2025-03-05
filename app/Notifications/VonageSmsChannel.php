<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class VonageSmsChannel
{
    protected $client;

    /**
     * Create a new Vonage SMS channel instance.
     */
    public function __construct()
    {
        $this->client = new Client(
            new Basic(
                config('services.vonage.key'),
                config('services.vonage.secret')
            )
        );
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$to = $notifiable->routeNotificationForVonage($notification)) {
            return;
        }

        $message = $notification->toVonage($notifiable);

        if (is_string($message)) {
            $message = [
                'content' => $message,
            ];
        }

        // Clean and format the phone number
        $to = $this->formatPhoneNumber($to);

        // Send the SMS message
        $this->client->sms()->send(
            new SMS(
                $to,
                config('services.vonage.from'),
                $message['content']
            )
        );
    }

    /**
     * Format the phone number to E.164 format.
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // For Bulgarian numbers, ensure they start with country code
        if (strlen($phoneNumber) === 9 && substr($phoneNumber, 0, 1) === '8') {
            // Convert format 089xxxxxxx to +359 (Bulgaria country code)
            return '359' . substr($phoneNumber, 1);
        }

        // If number already has country code (starts with 359)
        if (substr($phoneNumber, 0, 3) === '359') {
            return $phoneNumber;
        }

        // Default case - return as is if it already has country code
        return $phoneNumber;
    }
}
