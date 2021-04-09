<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\ApiKey;

class ApiKeyController extends Controller
{
    public function get_token(Request $request) {
        $api_key = (string) Str::uuid();
        try {
            DB::beginTransaction();

            $api_key_created = ApiKey::create([
                'api_key' => $api_key
            ]);
            $payload = collect($api_key_created)->except(['id']);
            DB::commit();
            return response()->json($payload);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function get_strict_key(Request $request) {
        $api_key = (string) Str::uuid();
        $ip = $request->ip();
        try {
            DB::beginTransaction();

            $api_key_created = ApiKey::create([
                'api_key' => $api_key,
                'related_ip_address' => $ip
            ]);
            $payload = collect($api_key_created)->except(['id']);
            DB::commit();
            return response()->json($api_key_created);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'error' => $th->getMessage()
            ], 400);
        }
    }
}
