<?php

namespace App\Http\Controllers;

use Exception;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    function apiCall($type, $url, $data = [], $headers = [])
    {
        try {
            $response = Http::acceptJson()->withHeaders(['user-api-key' => config('app.user_api_key')])->$type(config('app.api_base_url') . $url, $data);

            $response_data = $response->json();
            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => $response_data['message'] ?? '',
                    'data' => $response_data
                ];
            }

            if ($response->getStatusCode() == 401) {
                session()->flush();
                return [
                    'status' => false,
                    'message' => 'Logged out of the application.',
                    'data' => []
                ];
            }

            if ($response->getStatusCode() == 422) {
                return [
                    'status' => false,
                    'message' => $response_data['errors']['details']['title'],
                    'data' => []
                ];
            }

            return [
                'status' => false,
                'message' => $response_data['message'] ?? 'Something went wrong, Please try again later.',
                'data' => []
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }
}