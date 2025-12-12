<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Auth::routes();

Route::group(['middleware' => 'xss'], function () {
    // Best approach for SPA routing
    Route::get('/{any}', [App\Http\Controllers\HomeController::class, 'spa'])
        ->where('any', '(companies|project|users|dashboard|support|tasks|daily-reports|log-events).*');

    Route::get('/blank', [App\Http\Controllers\HomeController::class, 'blank'])->name('blank');

    Route::get('/amocrm/widgets/webhook', [App\Http\Controllers\Auth\AmoController::class, 'parseAmoCodes'])->name('amo');

    Route::post('/bot/telegram', [App\Http\Controllers\API\TelegramController::class, 'handler']);

    Route::get('/security/check', [App\Http\Controllers\HomeController::class, 'securityPage'])->name('security.check');
    Route::post('/code/check', [App\Http\Controllers\HomeController::class, 'checkSmsCode'])->name('check.sms_code');
    Route::get('/user/blocked', [App\Http\Controllers\HomeController::class, 'userBlocked'])->name('user_blocked');

    Route::post('/bitrix/install', [\App\Http\Controllers\Auth\BitrixController::class, 'install']);
});
