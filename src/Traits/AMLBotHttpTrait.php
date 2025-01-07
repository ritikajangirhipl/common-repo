<?php

namespace Vendor\CommonPackageAmlBot\Traits;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Implements all required APIs for AMLBot.
 * See https://kyc-docs.amlbot.com
 */
trait AMLBotHttpTrait
{
    /**
     * Returns the AMLBot Token configured in .env
     * @return ?string
     */
    public function amlbotApiToken(): ?string
    {
        
        return config('common.amlbot_api_token');;
    }

    /**
     * Determine whether AMLBot is enabled.
     * We consider AMLBot enabled if the API Token is configured in .env
     * @return bool
     */
    public function amlbotIsEnabled(): bool
    {
        return !empty($this->amlbotApiToken());
    }

    /**
     * Send HTTP POST request to AMLBot.
     * @param string $endpoint
     * @param array $formData
     * @return array
     * @throws Throwable
     */
    public function amlbotPostRequest(string $endpoint, array $formData): array
    {
        try {
            $url = 'https://kyc-api.amlbot.com/' . $endpoint;
            Log::info('amlbotPostRequest ' . $url . ' ' . print_r($formData, true));
            $client = new Client();
            $response = $client->request('POST', $url, [
                'headers' => $this->amlbotHeaders(),
                'form_params' => array_filter($formData)
            ]);
            return json_decode($response->getBody()->__toString() ?? '{}', true);
        } catch (RequestException $e) {
            throw $this->amlbotMapRequestException($e);
        }
    }

    /**
     * Send HTTP PATCH request to AMLBot.
     * @param string $endpoint
     * @param array $formData
     * @return array
     * @throws Throwable
     */
    public function amlbotPatchRequest(string $endpoint, array $formData): array
    {
        try {
            $url = 'https://kyc-api.amlbot.com/' . $endpoint;
            Log::info('amlbotPatchRequest ' . $url . ' ' . print_r($formData, true));
            $client = new Client();
            $response = $client->request('PATCH', $url, [
                'headers' => $this->amlbotHeaders(),
                'form_params' => array_filter($formData)
            ]);
            return json_decode($response->getBody()->__toString() ?? '{}', true);
        } catch (RequestException $e) {
            throw $this->amlbotMapRequestException($e);
        }
    }

    /**
     * POST a file to AMLBot.
     * @param string $filename
     * @return string
     * @throws AMLBotException
     * @throws Throwable
     */
    public function amlbotPostFile(string $filename): string
    {
        try {
            $url = 'https://kyc-api.amlbot.com/files';
            $filepath = storage_path($filename);
            Log::info('amlbotPostFile ' . $url . ' ' . $filepath);
            $client = new Client();
            $multipart = [];
            $multipart[] = ['name' => 'file', 'contents' => fopen($filepath, 'r')];
            Log::info('amlbotPostFile ' . print_r($multipart, true));
            $response = $client->request('POST', $url, [
                'headers' => $this->amlbotHeaders(),
                'multipart' => $multipart,
            ]);
            $json = json_decode($response->getBody()->__toString() ?? '{}', true);
            return $this->amlbotGetIdFromResponse('file_id', $json);
        } catch (RequestException $e) {
            throw $this->amlbotMapRequestException($e);
        }
    }

    /**
     * Get Form URL from AMLBot to start a new verification.
     * @param string $amlbotCorrelationId
     * @param string $correlationId
     * @return array
     * @throws GuzzleException
     * @throws Throwable
     */
    public function amlbotGetFormUrl(string $amlbotCorrelationId, string $correlationId): array
    {
        return $this->amlbotPostRequest(
            'forms/' . config('common.amlbot_verification_form_id') . '/urls',
            [
                'applicant_id' => $amlbotCorrelationId,
                'external_applicant_id' => $correlationId,
            ]
        );
    }

    /**
     * Extract an ID from an AMLBot response with expected ID key, e.g. "applicant_id".
     * @param $idKey
     * @param array $json
     * @return string
     * @throws Exception
     */
    public function amlbotGetIdFromResponse($idKey, array $json): string
    {
        if (!isset($json[$idKey])) {
            throw new Exception('AMLBot: ' . $idKey . ' not found in response');
        }
        return $json[$idKey];
    }

    /**
     * Get AMLBot standard headers, including Authorization Token.
     * @return string[]
     */
    private function amlbotHeaders(): array
    {
        return [
            'Authorization' => 'Token ' . $this->amlbotApiToken(),
            'Accept' => 'application/json'
        ];
    }

    /**
     * Map supported KYC document types such as "passport" to AMLBot document type such as "PASSPORT".
     * @param string $documentType
     * @return string|null
     */
    public function amlbotMapDocumentType(string $documentType): ?string
    {
        switch ($documentType) {
            case 'passport':
                return 'PASSPORT';
            case 'driving_license':
                return 'DRIVERS_LICENSE';
            case 'government_id':
                return 'GOVERNMENT_ID';
            default:
                Log::error('Unknown document_type ' . $documentType);
                return null;
        }
    }

    /**
     * Map AMLBot Exception to AMLBotException if error body can be parsed.
     * @param RequestException $e
     * @return Throwable
     */
    private function amlbotMapRequestException(RequestException $e): Throwable
    {
        Log::error('amlbotMapRequestException ' . $e->getCode() . ' ' . $e->getMessage());
        if ($e->hasResponse()) {
            $body = $e->getResponse()->getBody()->__toString() ?? '{}';
            try {
                $json = json_decode($body);
                Log::error('amlbotMapRequestException body: ' . print_r($json, true));
                $message = $this->amlbotErrorToString($json->errors[0]->message);
                return new AMLBotException($message, $e->getCode(), null, $json);
            } catch (Exception $e2) {
                $message = 'Something went wrong ' . $e2->getMessage();
            }
            return new AMLBotException($message, $e->getCode());
        }
        return $e;
    }

    /**
     * Maps an AMLBot error message key to a human-readable string.
     * See https://kyc-docs.amlbot.com/#errors
     * @param string $message
     * @return string
     */
    public function amlbotErrorToString(string $message): string
    {
        return match ($message) {
            'infected_file' => 'Potential virus infected file',
            'limit_file_size' => 'File too large',
            'inactive_account' => 'Account blocked',
            'flow' => 'Invalid action in flow',
            'edit_denied' => 'Edit denied',
            'delete_denied' => 'Delete denied',
            'applicant_exists' => 'Applicant already exists',
            'incompatible_file_for_recognition' => 'Unrecognised file format',
            'verification_exists' => 'Verification already exists',
            'insufficient_data' => 'Insufficient applicant data supplied',
            'broken_file' => 'File is damaged',
            'incompatible_file_format' => 'Unsupported file format',
            'image_small_resolution' => 'Image resolution is too small',
            'image_huge_resolution' => 'Image resolution is too large',
            default => $message,
        };
    }
}
