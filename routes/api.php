<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\SendEmailController;

use App\Services\SendEmailService;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('home', function () {
    return response()->json([
        'msg' => 'Welcome'
    ]);
});

Route::prefix('key')->group(function () {
    Route::post('send', [ApiKeyController::class, 'send_token']);
});

Route::prefix('email')->group(function () {
    Route::post('send', [SendEmailController::class, 'custom_email'])->middleware('related_ip_address');
});