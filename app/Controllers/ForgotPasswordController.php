<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Services\OtpService;
use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;

class ForgotPasswordController extends Controller
{
    protected User $users;
    protected OtpService $otpService;

    public function __construct()
    {
        $this->users = new User();
        $this->otpService = new OtpService();
    }

    public function showForgotForm(Request $request, Response $response): void
    {
        $this->view('auth/forgot-password', [
            'title' => t('title.forgot_password'),
            'errors' => flash('errors', []),
            'success' => flash('success'),
        ]);
    }

    public function sendOtp(Request $request, Response $response): void
    {
        $email = trim((string) $request->input('email', ''));

        $validator = new Validator();
        $validator
            ->required('email', $email, t('validation.email_required'))
            ->email('email', $email, t('validation.email_invalid'));

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::putOld(['email' => $email]);
            $this->redirect('/forgot-password');
        }

        if (!$this->users->existsByEmail($email)) {
            Session::flash('errors', [
                'email' => [t('flash.password.email_not_found')]
            ]);
            Session::putOld(['email' => $email]);
            $this->redirect('/forgot-password');
        }

        $sent = $this->otpService->sendForgotPasswordOtp($email);

        if (!$sent) {
            Session::flash('errors', [
                'general' => [t('flash.password.send_otp_failed', ['reason' => $this->otpService->lastError ?: 'Lỗi gửi email'])]
            ]);
            Session::putOld(['email' => $email]);
            $this->redirect('/forgot-password');
        }

        Session::set('password_reset_email', $email);
        Session::clearOld();
        Session::flash('success', t('flash.password.otp_sent'));

        $this->redirect('/forgot-password/verify');
    }

    public function showVerifyForm(Request $request, Response $response): void
    {
        $email = Session::get('password_reset_email');

        if (!$email) {
            Session::flash('errors', [
                'general' => [t('flash.password.session_invalid_request_again')]
            ]);
            $this->redirect('/forgot-password');
        }

        $this->view('auth/verify-otp', [
            'title' => t('title.verify_otp'),
            'errors' => flash('errors', []),
            'success' => flash('success'),
            'email' => $email,
        ]);
    }

    public function verifyOtp(Request $request, Response $response): void
    {
        $email = Session::get('password_reset_email');

        if (!$email) {
            Session::flash('errors', [
                'general' => [t('flash.password.session_invalid')]
            ]);
            $this->redirect('/forgot-password');
        }

        $otp = trim((string) $request->input('otp_code', ''));

        $validator = new Validator();
        $validator
            ->required('otp_code', $otp, t('validation.otp_required'))
            ->regex('otp_code', $otp, '/^[0-9]{6}$/', t('validation.otp_digits'));

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/forgot-password/verify');
        }

        $otpRecord = $this->otpService->verifyForgotPasswordOtp($email, $otp);

        if (!$otpRecord) {
            Session::flash('errors', [
                'otp_code' => [t('flash.password.otp_invalid')]
            ]);
            $this->redirect('/forgot-password/verify');
        }

        $this->otpService->markOtpUsed((int) $otpRecord['id']);
        Session::set('password_reset_verified', true);

        Session::flash('success', t('flash.password.otp_verified'));
        $this->redirect('/reset-password');
    }

    public function showResetForm(Request $request, Response $response): void
    {
        $email = Session::get('password_reset_email');
        $verified = Session::get('password_reset_verified', false);

        if (!$email || !$verified) {
            Session::flash('errors', [
                'general' => [t('flash.password.otp_not_verified')]
            ]);
            $this->redirect('/forgot-password');
        }

        $this->view('auth/reset-password', [
            'title' => t('title.reset_password'),
            'errors' => flash('errors', []),
            'success' => flash('success'),
        ]);
    }

    public function resetPassword(Request $request, Response $response): void
    {
        $email = Session::get('password_reset_email');
        $verified = Session::get('password_reset_verified', false);

        if (!$email || !$verified) {
            Session::flash('errors', [
                'general' => [t('flash.password.reset_session_invalid')]
            ]);
            $this->redirect('/forgot-password');
        }

        $password = (string) $request->input('password', '');
        $passwordConfirmation = (string) $request->input('password_confirmation', '');

        $validator = new Validator();
        $validator
            ->required('password', $password, t('validation.password_required'))
            ->regex(
                'password',
                $password,
                '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/',
                t('validation.password_strong')
            )
            ->confirm(
                'password_confirmation',
                $password,
                $passwordConfirmation,
                t('validation.password_confirmed')
            );

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/reset-password');
        }

        $updated = $this->users->updatePasswordByEmail(
            $email,
            password_hash($password, PASSWORD_DEFAULT)
        );

        if (!$updated) {
            Session::flash('errors', [
                'general' => [t('flash.password.reset_failed')]
            ]);
            $this->redirect('/reset-password');
        }

        Session::remove('password_reset_email');
        Session::remove('password_reset_verified');

        Session::flash('success', t('flash.password.reset_success'));
        $this->redirect('/login');
    }
}
