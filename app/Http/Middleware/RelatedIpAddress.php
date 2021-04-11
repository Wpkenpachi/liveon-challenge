<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Repositories\ApiKeyRepository;
use App\Services\ClientRequestService;

class RelatedIpAddress
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->hasHeader('authorization')) return response()->json(['error' => 'Unauthorizted Request. Missing authorization header'], 401);
        $x_api_token = $request->header('authorization');
        if (!$x_api_token) return response()->json(['error' => 'Unauthorizted Request'], 401);

        $token_record = ApiKeyRepository::fetch_token_record($x_api_token);

        // If has not related ip address to strict access, continue the request
        if (!$token_record && !$token_record->related_ip_address) return $next($request);

        $client_ip = ClientRequestService::getIp();
        $client_ip = $client_ip ? $client_ip : $request->ip(); // ->getIp() for balancers and ->ip() for direct request
        $allowed_ips = explode(";", $token_record->related_ip_address);

        if (!in_array($client_ip, $allowed_ips)) {
            return response()->json([
                'error' => 'Unauthorized Ip Address for this Request'
            ], 401);
        }

        return $next($request);
    }
}
