<?php
namespace Vendor\CommonPackage\Auth;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class AuthService
{
    public static function handleLoginForm(array $sessionData = [], array $cookies = [])
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
            $UserLoginService = App::make('App\Services\UserLoginService');
            $result = $UserLoginService->IAMlogout();
            $statusCode = $result->getStatusCode();
            if ($statusCode == 200) {
                if (isset($twoFactorAuthStatus['data']['2fa_status']) && !$twoFactorAuthStatus['data']['2fa_status']) {
                    auth()->logout();
                    Session::flush();
                }
            }
        }

        session()->put('previous_url', url()->previous());

        return compact('credentials', 'remember');
    }
}
