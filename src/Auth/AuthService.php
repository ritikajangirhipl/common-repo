<?php
namespace Vendor\CommonPackage\Auth;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Vendor\CommonPackage\Services\UserLoginService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class AuthService
{
    public static function handleLoginForm(array $data = [])
    {
        $validator = Validator::make($data, [
            'favicon' => 'required',
            'loginbg' => 'required',
            'logo' => 'required',
            'infoIcon' => 'required',
            'loginRoute' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }  

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
        $validator = Validator::make($data, [
            'dashboardRoute' => 'required',
            '2faRoute' => 'required',
            'pendingVerificationRoute' => 'required',
            'requestData' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }   

        if(session()->has("logged_in_user_detail")){
            return response()->json(["status" => true, "message" => "",'url'=> $data['dashboardRoute']], 200);
        }
        $cookies = null;
        $UserLoginService = new UserLoginService;
        $result =  $UserLoginService->IAMlogin($data['requestData']);
        if($result['code'] == 200){
            if($result['response']['data']['user']['status'] == 'active'){
                $verificationCode = generateRandom2FaString(config('common.two_factor_auth_code_length'));
                $result['response']['data']['2fa_status'] = false;
                $result['response']['data']['2fa_code'] = encrypt($verificationCode);
                Artisan::call('sending:2fa', [
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

    public static function handleLogout(array $data = [], $tokenInvalid = null)
    {
        $validator = Validator::make($data, [
            'loginRoute' => 'required',
            'requestData' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }    

        $UserLoginService = new UserLoginService;
        $result =  $UserLoginService->IAMlogout();
        $statusCode = $result->getStatusCode();
        if($statusCode == 200){
            if($result->original['code'] == 200){
                auth()->logout();
                request()->session()->invalidate();
                Session::flush();
                if(isset($data['requestData']['message']) && $data['requestData']['message'] == 'unauthorized_user'){
                    return redirect($data['loginRoute'])->with(['alert-type'=>'error','message'=>'Unauthorized user!']);
                }else if(!is_null($tokenInvalid)){
                    return response()->json(["status" => false, "message" => "Jwt Token is invalid, so you are logout forcefully!",'url'=> $data['loginRoute']],400);
                }else if(isset($data['requestData']['sleep_mode']) && $data['requestData']['sleep_mode']){
                    return redirect($data['loginRoute'])->with(['alert-type'=>'success','message'=>'Logout successfully!']);
                }else if(isset($data['requestData']['message']) && $data['requestData']['message'] == 'invalid_token'){
                    return redirect($data['loginRoute'])->with(['alert-type'=>'error','message'=>'Jwt Token is invalid, so you are logout forcefully!']);
                }
                return response()->json(["status" => true, "message" => "Logout successfully!",'url'=> $data['loginRoute']]);
            }
        }else{
            return response()->json(["status" => false, "message" => "Something went wrong!"],400);
        }
    }
}
