<?php
namespace Vendor\CommonPackage\Auth;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Vendor\CommonPackage\Services\UserLoginService;

class AuthService
{
    public static function handleLoginForm(array $sessionData = [], array $cookies = [], array $data = [])
    {
        $twoFactorAuthStatus = $sessionData['logged_in_user_detail'] ?? [];
        $credentials = [];
        $remember = false;

        // Process cookie data if provided
        if (!empty($cookies['user_credentials'])) {
            $decoded = json_decode($cookies['user_credentials'], true);
            if ($decoded) {
                $credentials = $decoded;
                $remember = true; 
            }
        }

        // 2FA Status Handling
        if (isset($twoFactorAuthStatus['data']['2fa_status']) && !$twoFactorAuthStatus['data']['2fa_status']) {
            $UserLoginService = new UserLoginService();
            $result = $UserLoginService->IAMlogout();
            $statusCode = $result->getStatusCode();
            if ($statusCode == 200) {
                if (isset($twoFactorAuthStatus['data']['2fa_status']) && !$twoFactorAuthStatus['data']['2fa_status']) {
                    auth()->logout();
                    Session::flush();
                }
            }
        }
        $data['appName'] = config('common.name');
        $data['appEnv'] = config('common.env');
        $data['passwordMinLength'] = config('common.password_min_length');
        $data['passwordMaxLength'] = config('common.password_max_length');
        session()->put('previous_url', url()->previous());

        return View::make('common::auth.login', compact('credentials', 'remember', 'data'));
    }
}
