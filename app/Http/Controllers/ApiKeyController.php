<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function get_token(Request $request) {
        $apy_key = (string) Str::uuid();
        $ip = $request->ip();
        return response()->json([
            'apy_key' => $apy_key,
            'ip' => $ip
        ]);
    }
}
