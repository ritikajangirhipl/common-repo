<?php

namespace Common\CommonPackage\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;

trait HttpRequestTrait
{
    public function IAMPostRequest($url, $formData)
    {
        try {

            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ];

            $client = new Client();

            $response = $client->request('POST', $url, [
                'json' => $formData,
                'headers' => $headers,
            ]);

            $body = $response->getBody()->__toString();
            $result = ['status' => true, 'code' => 200, 'message' => 'Successful', 'response' => json_decode($body, true)];
            return $result;
        } catch (ConnectException $e) {
            $result = ['status' => false, 'code' => $e->getCode(), 'message' => $e->getMessage()];
            Log::error('Error in api::'.$url.' (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
        } catch (ClientException $e) {
            $result = ['status' => false, 'code' => $e->getCode(), 'message' => json_decode($e->getResponse()->getBody())];
            Log::error('Error in api::'.$url.' (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
        } catch (Exception $e) {
            $result = ['status' => false, 'code' => $e->getCode(), 'message' => $e->getMessage()];
            Log::error('Error in api::'.$url.' (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
        }catch (Throwable $e) {
            $result = ['status' => false, 'code' => $e->getCode(), 'message' => $e->getMessage()];
            Log::error('Error in api::'.$url.' (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
        } 
 
        return $result;
    }

    public function IAMGetRequest($url)
    {
        $statusCode = 200;
        try {
          
            $loggedInUserDetails = session()->get('logged_in_user_detail');

            $headers = [
                'Authorization' => 'Bearer ' . $loggedInUserDetails['data']['access_token'],
                'Content-Type' => 'application/json'
            ];

            $client = new Client();

            $response = $client->request('GET', $url, ['headers' => $headers]);

            $body = $response->getBody()->__toString();

            Log::info('HTTP request to ' . $url . ' succeeded');
            $result = ['status' => true, 'code' => 200, 'message' => 'Logout successfully', 'response' => json_decode($body, true)];

            return response()->json($result);

        } catch (ConnectException $e) {
            $statusCode = $e->getCode();
            $response = ['status' => false, 'code' => $e->getCode(), 'message' => $e->getMessage()];
            Log::error('Error in api::'.$url.' (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
        } catch (ClientException $e) {
            $statusCode = $e->getCode();
            $response = ['status' => false, 'code' => $e->getCode(), 'message' => json_decode($e->getResponse()->getBody())];
            Log::error('Error in api::'.$url.' (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
        } catch (Exception $e) {
            $statusCode = $e->getCode();
            $response = ['status' => false, 'code' => $e->getCode(), 'message' => $e->getMessage()];
            Log::error('Error in api::'.$url.' (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
        }catch (Throwable $e) {
            $statusCode = $e->getCode();
            $response = ['status' => false, 'code' => $e->getCode(), 'message' => $e->getMessage()];
            Log::error('Error in api::'.$url.' (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
        }
        Log::info('HTTP request to ' . $url . ' failed');

        return response()->json($response,$statusCode);
    }
}
