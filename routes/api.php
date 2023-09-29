<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScreenRecordController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/screen-record', [ScreenRecordController::class, 'showScreenRecord'])->name('record.view');
Route::get('/screen-record/{id}', [ScreenRecordController::class, 'showScreenRecordId'])->name('recordid.view');
Route::post('/screen-record', [ScreenRecordController::class, 'screenRecordSave'])->name('record.save');
