<?php

namespace Vendor\CommonPackage\Services;

use Vendor\CommonPackage\Traits\HttpRequestTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserLoginService
{
    use HttpRequestTrait;

    private function iamApiUrl()
    {
        return config('common.iam_api_url');
    }

    public function IAMlogin($data)
    {
        $credentials = $this->getIAMCredentials($data);
        $url = $this->iamApiUrl() . '/login';
        Log::info('IAMlogin url = ' . $url);
        $response = $this->IAMPostRequest($url, $credentials);
        return $response;
    }

    private function getIAMCredentials($data): array
    {
        return ['email' => $data['email'], 'password' => $data['password']];
    }

    public function IAMlogout()
    {
        $url = $this->iamApiUrl() . '/logout';
        Log::info('IAMlogout url = ' . $url);
        $response = $this->IAMGetRequest($url);
        return $response;
    }

    public function IAMGetNewAccessToken($refreshToken)
    {
        $url = $this->iamApiUrl() . '/refresh/' . $refreshToken;
        Log::info('IAMGetNewAccessToken url = ' . $url);
        $response = $this->IAMGetRequest($url);
        return $response;
    }
}
