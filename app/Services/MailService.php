<?php

declare(strict_types=1);

namespace App\Services;

/**
 * MailService – sử dụng PHP mail() thuần thay cho PHPMailer.
 *
 * Lưu ý: Trên InfinityFree free plan, mail() có thể bị giới hạn.
 * Nếu không gửi được, tính năng OTP / hóa đơn sẽ bị tắt nhưng app vẫn chạy bình thường.
 */
class MailService
{
    public string $lastError = '';

    public function send(string $to, string $subject, string $htmlBody, string $plainBody = ''): bool
    {
        $fromAddress = (string) env('MAIL_FROM_ADDRESS', 'noreply@nautshop.com');
        $fromName    = (string) env('MAIL_FROM_NAME', 'Naut Shop');

        $boundary = '----=_Part_' . md5(uniqid('', true));

        $headers  = 'From: ' . $fromName . ' <' . $fromAddress . ">\r\n";
        $headers .= 'Reply-To: ' . $fromAddress . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '"' . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";

        $plain = $plainBody !== '' ? $plainBody : strip_tags($htmlBody);

        $body  = '--' . $boundary . "\r\n";
        $body .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
        $body .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
        $body .= chunk_split(base64_encode($plain)) . "\r\n";

        $body .= '--' . $boundary . "\r\n";
        $body .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
        $body .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
        $body .= chunk_split(base64_encode($htmlBody)) . "\r\n";

        $body .= '--' . $boundary . '--';

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        $result = @mail($to, $encodedSubject, $body, $headers);

        if (!$result) {
            $this->lastError = error_get_last()['message'] ?? 'mail() trả về false';
        }

        return $result;
    }
}
