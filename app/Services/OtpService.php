<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OtpCode;
use DateTime;

class OtpService
{
    protected OtpCode $otpCodes;
    protected MailService $mailService;
    public string $lastError = '';

    public function __construct()
    {
        $this->otpCodes = new OtpCode();
        $this->mailService = new MailService();
    }

    public function sendForgotPasswordOtp(string $email): bool
    {
        $this->otpCodes->invalidateOldOtps($email, 'forgot_password');

        $otp = (string) random_int(100000, 999999);
        $ttl = (int) env('OTP_TTL', 300);

        $expiresAt = (new DateTime())
            ->setTimestamp(time() + $ttl)
            ->format('Y-m-d H:i:s');

        $created = $this->otpCodes->create([
            'user_id' => null,
            'email' => $email,
            'purpose' => 'forgot_password',
            'otp_code' => $otp,
            'expires_at' => $expiresAt,
        ]);

        if (!$created) {
            $this->lastError = 'Hệ thống tạm thời không thể tạo mã xác thực.';
            return false;
        }

        $subject = 'Password Reset OTP - Naut Shop';

        $htmlBody = $this->buildOtpEmailTemplate($otp, $ttl);
        $plainBody = "Your password reset OTP is: {$otp}. This code is valid for " . (int) ($ttl / 60) . " minutes.";

        $sent = $this->mailService->send($email, $subject, $htmlBody, $plainBody);

        if (!$sent) {
            $this->lastError = $this->mailService->lastError;
        }

        return $sent;
    }

    public function verifyForgotPasswordOtp(string $email, string $otp): array|false
    {
        $record = $this->otpCodes->findLatestOtp($email, 'forgot_password', $otp);

        if (!$record) {
            return false;
        }

        if (!empty($record['used_at'])) {
            return false;
        }

        $expiresAt = strtotime((string) $record['expires_at']);

        if ($expiresAt === false || time() > $expiresAt) {
            return false;
        }

        return $record;
    }

    public function markOtpUsed(int $otpId): bool
    {
        return $this->otpCodes->markAsUsed($otpId);
    }

    protected function buildOtpEmailTemplate(string $otp, int $ttl): string
    {
        $minutes = (int) ceil($ttl / 60);

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
</head>
<body style="margin:0;padding:0;background-color:#f3eee7;font-family:\'Plus Jakarta Sans\',Arial,sans-serif;color:#142033;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f3eee7;padding:40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background-color:#fffdf9;border-radius:28px;overflow:hidden;box-shadow:0 4px 24px rgba(20,32,51,0.06);">

                    <!-- Content -->
                    <tr>
                        <td style="padding:48px 44px 40px;" align="center">
                            <!-- Eyebrow -->
                            <div style="font-family:Arial,sans-serif;font-size:11px;line-height:16px;color:#b07a45;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;margin-bottom:18px;">
                                Security Verification
                            </div>

                            <!-- Heading -->
                            <div style="font-family:Georgia,\'Times New Roman\',serif;font-size:30px;line-height:38px;color:#142033;font-weight:700;margin-bottom:14px;">
                                Password Reset Request
                            </div>

                            <!-- Description -->
                            <div style="font-family:Arial,sans-serif;font-size:15px;line-height:26px;color:#6b7280;max-width:460px;margin-bottom:32px;">
                                We received a request to reset the password for your account. Use the OTP code below to continue the verification process.
                            </div>

                            <!-- Summary box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f9f5ef;border:1px solid #e8ddd0;border-radius:18px;max-width:480px;">
                                <tr>
                                    <td style="padding:20px 28px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <!-- OTP Code -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">
                                                    OTP Code
                                                </td>
                                                <td style="padding:14px 0;font-family:Georgia,\'Times New Roman\',serif;font-size:28px;line-height:34px;color:#142033;font-weight:700;letter-spacing:6px;text-align:right;border-bottom:1px solid #e8ddd0;">
                                                    ' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '
                                                </td>
                                            </tr>
                                            <!-- Expires in -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">
                                                    Expires in
                                                </td>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;border-bottom:1px solid #e8ddd0;">
                                                    ' . $minutes . ' minutes
                                                </td>
                                            </tr>
                                            <!-- Purpose -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;">
                                                    Purpose
                                                </td>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;">
                                                    Password Reset
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Footer note -->
                            <div style="font-family:Arial,sans-serif;font-size:13px;line-height:22px;color:#9a8a78;margin-top:28px;">
                                Please do not share this code with anyone.<br>
                                If you did not request a password reset, you can safely ignore this email.
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
}
