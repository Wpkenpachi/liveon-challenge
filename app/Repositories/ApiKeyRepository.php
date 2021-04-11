<?php

namespace App\Repositories;

use App\Models\ApiKey;
use Illuminate\Support\Facades\DB;

class ApiKeyRepository {
    protected $model;

    public function __construct() {
        $this->model = app(ApiKey::class);
    }

    public static function fetch_token_record($token) {
        return ApiKey::where('api_key', $token)->first();
    }

    public function store_strict_token($api_key, $related_ip_address) {
        try {
            DB::beginTransaction();
            $api_key_created = $this->model::updateOrCreate(
                [
                    'api_key' => $api_key
                ],
                [
                'api_key' => $api_key,
                'related_ip_address' => $related_ip_address
                ]
            );
            $payload = collect($api_key_created)->except(['id']);
            DB::commit();
            return $payload;
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}