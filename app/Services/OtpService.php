<?php

namespace App\Services;

use App\Models\Otp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class OtpService
{
    /**
     * Generate and send OTP
     */
    public function generateAndSendOtp(string $identifier, string $type): string
    {
        // Generate 6 digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP
        Otp::create([
            'identifier' => $identifier,
            'type' => $type,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(10) // OTP valid for 10 minutes
        ]);

        // Send OTP based on type
        $this->sendOtp($identifier, $otp, $type);

        return $otp;
    }

    /**
     * Send OTP via different channels
     */
    private function sendOtp(string $identifier, string $otp, string $type): void
    {
        switch ($type) {
            case 'email':
                $this->sendEmailOtp($identifier, $otp);
                break;
            case 'mobile':
                $this->sendSmsOtp($identifier, $otp);
                break;
            case 'whatsapp':
                $this->sendWhatsappOtp($identifier, $otp);
                break;
        }
    }

    /**
     * Send OTP via email
     */
    private function sendEmailOtp(string $email, string $otp): void
    {
        // Implement email sending logic here
        // You can use Laravel's Mail facade
        Mail::raw("Your OTP is: {$otp}", function($message) use ($email) {
            $message->to($email)
                   ->subject('Fresh Binge - Your OTP');
        });
    }

    /**
     * Send OTP via SMS
     */
    private function sendSmsOtp(string $mobile, string $otp): void
    {
        // Implement SMS sending logic here
        // You'll need to integrate with an SMS service provider
        // Example using a generic HTTP client:
        /*
        Http::post('sms-provider-url', [
            'phone' => $mobile,
            'message' => "Your Fresh Binge OTP is: {$otp}"
        ]);
        */
    }

    /**
     * Send OTP via WhatsApp
     */
    private function sendWhatsappOtp(string $mobile, string $otp): void
    {
        // Implement WhatsApp sending logic here
        // You'll need to integrate with WhatsApp Business API
        // Example using a generic HTTP client:
        /*
        Http::post('whatsapp-provider-url', [
            'phone' => $mobile,
            'message' => "Your Fresh Binge OTP is: {$otp}"
        ]);
        */
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(string $identifier, string $type, string $otp): bool
    {
        $otpRecord = Otp::where('identifier', $identifier)
            ->where('type', $type)
            ->where('is_verified', false)
            ->latest()
            ->first();

        if (!$otpRecord || !$otpRecord->isValid($otp)) {
            return false;
        }

        $otpRecord->update(['is_verified' => true]);
        return true;
    }

    /**
     * Check if WhatsApp number exists
     */
    public function isWhatsappNumber(string $mobile): bool
    {
        // Implement WhatsApp number verification logic here
        // You'll need to integrate with WhatsApp Business API
        // This is a placeholder that always returns true
        return true;
    }
}
