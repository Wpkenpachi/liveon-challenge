<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Repositories\ApiKeyRepository;
use Illuminate\Support\Facades\Validator;
use App\Services\ClientRequestService;
use App\Services\SendEmailService;

class ApiKeyController extends Controller
{
    protected $ApiKeyRepository;

    public function __construct() {
        $this->ApiKeyRepository = new ApiKeyRepository();
    }

    public function send_token(Request $request) {
        // Getting/Generating data
        $api_key = $request->get('key');
        $related_ip_address = $request->get('related_ip_address');
        $related_ip_address = $related_ip_address ? implode(";", $related_ip_address) : null;
        $data = [
            'api_key' =>  $api_key,
            'related_ip_address' => $request->get('related_ip_address')
        ];

        // Validating
        $validator = Validator::make($data, [
            'api_key' => 'required',
            'related_ip_address' => 'nullable|array'
        ], [
            'api_key.required' => 'ApiKey Could Not Be Geenrated',
            'related_ip_address.required' => 'Client Ip Could not be found'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Store Token
        try {
            $token_validation = SendEmailService::validate_key($request->get('key'));
            if ($token_validation != 200) throw new \Exception("Invalid Api Key");
            $response = $this->ApiKeyRepository->store_strict_token($api_key, $related_ip_address);
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ]);
        }
    }
}
