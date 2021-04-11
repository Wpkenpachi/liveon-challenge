<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Mail;
use App\Mail\ClientEmail;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/mail', function () {
    return new App\Mail\ClientEmail([
        'html' => "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta http-equiv='X-UA-Compatible' content='IE=edge'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>CustomEmail</title></head><body><h1> Olá Wesley </h1></body></html>",
        'sender' => "wesley.santos@zappts.com",
        'subject' => "Just Kidding"
    ]);
});