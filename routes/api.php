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


Route::post('/screen-recording', [ScreenRecordController::class, 'screenRecordSave'])
    ->name('recording.save')
    ->middleware('web');
Route::get('/view-recording', [ScreenRecordController::class, 'showScreenRecord'])
->name('recording.view');
Route::get('/screen-recording/{id}', [ScreenRecordController::class, 'showScreenRecordId'])
->name('recordingid.view');
Route::delete('/screen-recording/{id}', [ScreenRecordController::class, 'deleteScreenRecording'])
->name('recording.delete');
