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

    private function getIp(){
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
    }

    public function get_strict_key(Request $request) {
        $api_key = (string) Str::uuid();
        $ip = $this->getIp();
        try {
            DB::beginTransaction();

            $api_key_created = ApiKey::create([
                'api_key' => $api_key,
                'related_ip_address' => $ip
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
}
