<?php
namespace Vendor\CommonPackage\Auth;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Vendor\CommonPackage\Services\UserLoginService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cookie;

class AuthService
{
    public static function handleLoginForm(array $sessionData = [], array $cookies = [], array $data = [])
    {
        $twoFactorAuthStatus = session()->has("logged_in_user_detail") ? session()->get("logged_in_user_detail") : [];
        $credentials = [];
        $remember = false;

        // Process cookie data if provided
        if (request()->hasCookie('user_credentials')) {
            $decoded = json_decode(request()->cookie('user_credentials'), true);
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

    public static function handleLogin(array $data = [])
    {
        if(session()->has("logged_in_user_detail")){
            return response()->json(["status" => true, "message" => "",'url'=> $data['dashboardRoute']], 200);
        }
        $cookies = null;
        $UserLoginService = new UserLoginService;
        $result =  $UserLoginService->IAMlogin($data['requestData']);
        if($result['code'] == 200){
            if($result['response']['data']['user']['status'] == 'active'){
                $verificationCode = generateRandomString(config('common.two_factor_auth_code_length'));
                $result['response']['data']['2fa_status'] = false;
                $result['response']['data']['2fa_code'] = encrypt($verificationCode);
                Artisan::call($data['2faEmailCommand'], [
                    'email' => $result['response']['data']['user']['email'],
                    'code' =>  $verificationCode
                ]);
                $cookies = self::saveLoggedInUserDetailInSession(array_merge($result['response'], ['email' => $data['requestData']['email'], 'password' => $data['requestData']['password'],'remember_me' => $data['requestData']['remember_me']]));
                $url = $data['2faRoute'];
                $response = ["status" => true, "message" => "Login successful",'url'=>$url];
            } else if($result['response']['data']['user']['status'] == 'pending'){
                $response = ["status" => true, "message" => "Account is not active.",'url'=>$data['pendingVerificationRoute']];
            } else {
                $response = ["status" => false, "message" => "Account is not active."];
                return response()->json($response, 403);
            }
            if($cookies) return response()->json($response, 200)->withCookie($cookies);
            else return response()->json($response, 200);
        }else{
            return response()->json($result, $result['code'] ?? 404);
        }
    }

    private static function saveLoggedInUserDetailInSession($result)
    {
        $cookies = null;
        if($result['remember_me'] && $result['remember_me'] == 'true'){
            $credentials = [
                'email' => $result['email'],
                'password' => $result['password'],
            ];

            $cookies = Cookie::queue(Cookie::make('user_credentials', json_encode($credentials), 43200));
        }else{
            $cookies = Cookie::forget('user_credentials');
        }
        session()->put('logged_in_user_detail',$result);
        session()->save();
        return $cookies;
    }
}
