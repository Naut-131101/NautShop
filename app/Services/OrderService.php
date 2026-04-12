<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Core\Session;

class OrderService
{
    protected const SESSION_PENDING_ORDER_ID = 'pending_order_id';
    protected const SESSION_LAST_ORDER_ID = 'last_order_id';

    protected Order $orders;
    protected MailService $mail;
    protected VietQrService $vietQr;

    public function __construct()
    {
        $this->orders = new Order();
        $this->mail = new MailService();
        $this->vietQr = new VietQrService();
    }

    public function createPendingOrder(array $customer, array $items, array $totals): array
    {
        $user = auth() ?? [];
        $order = $this->orders->createPending((int) ($user['id'] ?? 0), $customer, $items, $totals);

        Session::set(self::SESSION_PENDING_ORDER_ID, (int) ($order['id'] ?? 0));

        return $order;
    }

    public function getPendingOrderForSession(): ?array
    {
        $user = auth() ?? [];
        $orderId = (int) Session::get(self::SESSION_PENDING_ORDER_ID, 0);

        if ($orderId <= 0 || empty($user['id'])) {
            return null;
        }

        $order = $this->orders->findByIdForUser($orderId, (int) $user['id']);

        if (!$order || ($order['order_status'] ?? '') !== Order::STATUS_PENDING_PAYMENT) {
            $this->clearPendingOrderSession();
            return null;
        }

        return $order;
    }

    public function getLastSuccessfulOrder(): ?array
    {
        $user = auth() ?? [];
        $orderId = (int) Session::get(self::SESSION_LAST_ORDER_ID, 0);

        if ($orderId <= 0 || empty($user['id'])) {
            return null;
        }

        return $this->orders->findByIdForUser($orderId, (int) $user['id']);
    }

    public function listOrdersForCurrentUser(?string $status = null): array
    {
        $user = auth() ?? [];
        $userId = (int) ($user['id'] ?? 0);

        if ($userId <= 0) {
            return [];
        }

        return $this->orders->listByUser($userId, $status);
    }

    public function countOrdersByStatusForCurrentUser(): array
    {
        $user = auth() ?? [];
        $userId = (int) ($user['id'] ?? 0);

        if ($userId <= 0) {
            return [
                'all' => 0,
                Order::STATUS_PENDING_PAYMENT => 0,
                Order::STATUS_PAID => 0,
                Order::STATUS_CANCELLED => 0,
            ];
        }

        return $this->orders->countByStatusForUser($userId);
    }

    public function markPendingOrderPaid(array $order): ?array
    {
        $user = auth() ?? [];
        $orderId = (int) ($order['id'] ?? 0);
        $userId = (int) ($user['id'] ?? 0);

        if ($orderId <= 0 || $userId <= 0) {
            return null;
        }

        $paymentReference = $this->fakePaymentReference((string) ($order['payment_method'] ?? ''));
        $updated = $this->orders->markPaid($orderId, $userId, $paymentReference);

        if (!$updated) {
            return null;
        }

        $this->clearPendingOrderSession();
        Session::set(self::SESSION_LAST_ORDER_ID, $orderId);

        return $this->orders->findByIdForUser($orderId, $userId);
    }

    public function cancelPendingOrder(?int $orderId, string $reason): bool
    {
        $user = auth() ?? [];
        $targetOrderId = $orderId ?: (int) Session::get(self::SESSION_PENDING_ORDER_ID, 0);
        $userId = (int) ($user['id'] ?? 0);

        if ($targetOrderId <= 0 || $userId <= 0) {
            return false;
        }

        $cancelled = $this->orders->cancelPending($targetOrderId, $userId, $reason);

        if ($cancelled || (int) Session::get(self::SESSION_PENDING_ORDER_ID, 0) === $targetOrderId) {
            $this->clearPendingOrderSession();
        }

        return $cancelled;
    }

    public function clearPendingOrderSession(): void
    {
        Session::remove(self::SESSION_PENDING_ORDER_ID);
    }

    public function paymentMethods(): array
    {
        return [
            Order::METHOD_VISA => [
                'label' => t('payment.method_visa'),
                'description' => t('payment.method_visa_description'),
            ],
            Order::METHOD_VIETQR => [
                'label' => t('payment.method_vietqr'),
                'description' => t('payment.method_vietqr_description'),
            ],
        ];
    }

    public function orderStatusOptions(): array
    {
        return [
            '' => t('orders.status_all'),
            Order::STATUS_PENDING_PAYMENT => t('orders.status_pending'),
            Order::STATUS_PAID => t('orders.status_paid'),
            Order::STATUS_CANCELLED => t('orders.status_cancelled'),
        ];
    }

    public function orderStatusLabel(string $status): string
    {
        return $this->orderStatusOptions()[$status] ?? $status;
    }

    public function orderStatusClass(string $status): string
    {
        return match ($status) {
            Order::STATUS_PAID => 'is-paid',
            Order::STATUS_CANCELLED => 'is-cancelled',
            default => 'is-pending',
        };
    }

    public function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            Order::PAYMENT_STATUS_PAID => t('payment.status_paid'),
            Order::PAYMENT_STATUS_FAILED => t('payment.status_failed'),
            default => t('payment.awaiting_payment'),
        };
    }

    public function cancellationReasonLabel(?string $reason): string
    {
        return match ((string) $reason) {
            'logout_before_payment' => t('orders.cancel_reason_logout'),
            'user_cancelled_payment' => t('orders.cancel_reason_user'),
            'customer_left_payment_page' => t('orders.cancel_reason_abandon'),
            default => (string) $reason,
        };
    }

    public function buildPaymentViewData(array $order): array
    {
        $transferContent = $this->sanitizeTransferText((string) ($order['order_code'] ?? ''));
        $accountNo = (string) env('VIETQR_ACCOUNT_NO', '113366668888');
        $accountName = (string) env('VIETQR_ACCOUNT_NAME', 'NAUT SHOP DEMO');
        $amount = (int) round((float) ($order['total_amount'] ?? 0));
        $qrPayload = $this->vietQr->generate([
            'amount' => $amount,
            'addInfo' => $transferContent,
            'accountNo' => $accountNo,
            'accountName' => $accountName,
        ]);

        return [
            'methods' => $this->paymentMethods(),
            'selectedMethod' => (string) ($order['payment_method'] ?? Order::METHOD_VISA),
            'vietQrImageUrl' => (string) ($qrPayload['imageSrc'] ?? ''),
            'bankId' => (string) ($qrPayload['bankCode'] ?? ''),
            'bankName' => (string) ($qrPayload['bankName'] ?? ''),
            'acqId' => (string) ($qrPayload['acqId'] ?? ''),
            'accountNo' => (string) ($qrPayload['accountNo'] ?? $accountNo),
            'accountName' => (string) ($qrPayload['accountName'] ?? $accountName),
            'providerLabel' => (string) ($qrPayload['providerLabel'] ?? 'VietQR'),
            'isOfficialApi' => (bool) ($qrPayload['isOfficialApi'] ?? false),
            'transferContent' => $transferContent,
        ];
    }

    public function sendInvoiceEmail(array $order): bool
    {
        $user = auth() ?? [];
        $email = (string) ($order['customer_email'] ?? '');

        if ($email === '') {
            return false;
        }

        $subject = 'Invoice - Naut Shop #' . (string) ($order['order_code'] ?? '');
        $htmlBody = $this->buildInvoiceHtml($order);
        $plainBody = $this->buildInvoicePlainText($order);
        $sent = $this->mail->send($email, $subject, $htmlBody, $plainBody);

        if ($sent) {
            $this->orders->markInvoiceSent((int) ($order['id'] ?? 0), (int) ($user['id'] ?? 0));
        }

        return $sent;
    }

    protected function buildInvoiceHtml(array $order): string
    {
        $rows = '';
        $itemCount = 0;

        foreach (($order['items'] ?? []) as $item) {
            $itemCount += (int) ($item['quantity'] ?? 0);
            $rows .= sprintf(
                '<tr>
                    <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">%s (x%s)</td>
                    <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;border-bottom:1px solid #e8ddd0;">%s</td>
                </tr>',
                e((string) ($item['product_name'] ?? '')),
                e((string) ($item['quantity'] ?? '0')),
                e(format_price((float) ($item['line_total'] ?? 0)))
            );
        }

        $addressHtml = nl2br(e((string) ($order['shipping_address'] ?? '')));
        $noteHtml = trim((string) ($order['note'] ?? '')) !== ''
            ? '<tr>
                    <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">Note</td>
                    <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;border-bottom:1px solid #e8ddd0;">' . e((string) ($order['note'] ?? '')) . '</td>
                </tr>'
            : '';

        return '<!doctype html>
<html lang="en">
<body style="margin:0;padding:0;background-color:#f3eee7;font-family:\'Plus Jakarta Sans\',Arial,sans-serif;color:#142033;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">
        Naut Shop invoice for order ' . e((string) ($order['order_code'] ?? '')) . ' has been successfully paid.
    </div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f3eee7;margin:0;padding:0;">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:620px;background-color:#fffdf9;border-radius:28px;overflow:hidden;box-shadow:0 4px 24px rgba(20,32,51,0.06);">

                    <!-- Content -->
                    <tr>
                        <td style="padding:48px 44px 40px;" align="center">
                            <!-- Eyebrow -->
                            <div style="font-family:Arial,sans-serif;font-size:11px;line-height:16px;color:#b07a45;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;margin-bottom:18px;">
                                Order Placed Successfully
                            </div>

                            <!-- Heading -->
                            <div style="font-family:Georgia,\'Times New Roman\',serif;font-size:30px;line-height:38px;color:#142033;font-weight:700;margin-bottom:14px;">
                                Your Order Has Been Confirmed
                            </div>

                            <!-- Description -->
                            <div style="font-family:Arial,sans-serif;font-size:15px;line-height:26px;color:#6b7280;max-width:460px;margin-bottom:32px;">
                                Thank you for shopping at Naut Shop. We will contact you to confirm and prepare your shipment as soon as possible.
                            </div>

                            <!-- Summary box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f9f5ef;border:1px solid #e8ddd0;border-radius:18px;max-width:500px;">
                                <tr>
                                    <td style="padding:20px 28px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <!-- Order Code -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">
                                                    Order Code
                                                </td>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;border-bottom:1px solid #e8ddd0;">
                                                    ' . e((string) ($order['order_code'] ?? '')) . '
                                                </td>
                                            </tr>
                                            <!-- Created At -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">
                                                    Created At
                                                </td>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;border-bottom:1px solid #e8ddd0;">
                                                    ' . e((string) ($order['placed_at'] ?? '')) . '
                                                </td>
                                            </tr>
                                            <!-- Payment Status -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">
                                                    Payment Status
                                                </td>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;border-bottom:1px solid #e8ddd0;">
                                                    Paid
                                                </td>
                                            </tr>
                                            <!-- Transaction ID -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">
                                                    Transaction ID
                                                </td>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;border-bottom:1px solid #e8ddd0;">
                                                    ' . e((string) ($order['payment_reference'] ?? '')) . '
                                                </td>
                                            </tr>
                                            <!-- Customer -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">
                                                    Customer
                                                </td>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;border-bottom:1px solid #e8ddd0;">
                                                    ' . e((string) ($order['customer_name'] ?? '')) . '
                                                </td>
                                            </tr>
                                            <!-- Address -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">
                                                    Address
                                                </td>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;border-bottom:1px solid #e8ddd0;">
                                                    ' . e((string) ($order['shipping_address'] ?? '')) . '
                                                </td>
                                            </tr>
                                            <!-- Payment Method -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">
                                                    Payment Method
                                                </td>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;border-bottom:1px solid #e8ddd0;">
                                                    ' . e(strtoupper((string) ($order['payment_method'] ?? ''))) . '
                                                </td>
                                            </tr>
                                            <!-- Note -->
                                            ' . $noteHtml . '
                                            <!-- Products -->
                                            ' . $rows . '
                                            <!-- Subtotal -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">
                                                    Subtotal
                                                </td>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;border-bottom:1px solid #e8ddd0;">
                                                    ' . e(format_price((float) ($order['totals']['subtotal'] ?? 0))) . '
                                                </td>
                                            </tr>
                                            <!-- Shipping -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">
                                                    Shipping
                                                </td>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;border-bottom:1px solid #e8ddd0;">
                                                    ' . e(format_price((float) ($order['totals']['shipping'] ?? 0))) . '
                                                </td>
                                            </tr>
                                            <!-- Discount -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;border-bottom:1px solid #e8ddd0;">
                                                    Discount
                                                </td>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#142033;font-weight:700;text-align:right;border-bottom:1px solid #e8ddd0;">
                                                    -' . e(format_price((float) ($order['totals']['discount'] ?? 0))) . '
                                                </td>
                                            </tr>
                                            <!-- Total -->
                                            <tr>
                                                <td style="padding:14px 0;font-family:Arial,sans-serif;font-size:14px;line-height:22px;color:#6b7280;">
                                                    Total
                                                </td>
                                                <td style="padding:14px 0;font-family:Georgia,\'Times New Roman\',serif;font-size:22px;line-height:28px;color:#b07a45;font-weight:700;text-align:right;">
                                                    ' . e(format_price((float) ($order['totals']['total'] ?? 0))) . '
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Footer note -->
                            <div style="font-family:Arial,sans-serif;font-size:13px;line-height:22px;color:#9a8a78;margin-top:28px;">
                                Need help? Reply to this email and Naut Shop will review your transaction.
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

    protected function buildInvoicePlainText(array $order): string
    {
        $lines = [
            'NAUT SHOP',
            'Order Code: ' . (string) ($order['order_code'] ?? ''),
            'Customer: ' . (string) ($order['customer_name'] ?? ''),
            'Email: ' . (string) ($order['customer_email'] ?? ''),
            'Phone: ' . (string) ($order['customer_phone'] ?? ''),
            'Address: ' . (string) ($order['shipping_address'] ?? ''),
            'Payment Method: ' . strtoupper((string) ($order['payment_method'] ?? '')),
            '',
            'Order Details:',
        ];

        foreach (($order['items'] ?? []) as $item) {
            $lines[] = sprintf(
                '- %s x%s: %s',
                (string) ($item['product_name'] ?? ''),
                (string) ($item['quantity'] ?? '0'),
                format_price((float) ($item['line_total'] ?? 0))
            );
        }

        $lines[] = '';
        $lines[] = 'Total: ' . format_price((float) ($order['totals']['total'] ?? 0));

        return implode("\n", $lines);
    }

    protected function sanitizeTransferText(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9 ]/', '', $value) ?? '';
        $value = trim($value);

        return substr($value !== '' ? $value : 'NAUTSHOP', 0, 50);
    }

    public function fakePaymentReference(string $method): string
    {
        $prefix = $method === Order::METHOD_VIETQR ? 'VQR' : 'VIS';
        $stamp = date('Ymd');
        $blockA = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $blockB = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));

        return $prefix . '-' . $stamp . '-' . $blockA . '-' . $blockB;
    }
}
