<?php
use App\Http\Controllers\ScreenRecordController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
});

Route::post('/upload-chunk', [ScreenRecordController::class, 'chunkUpload'])->name('upload.chunk');
Route::post('/complete-upload', [ScreenRecordController::class, 'completeUpload'])->name('complete.upload');
Route::post('/screen-record', [ScreenRecordController::class, 'screenRecordSave'])
    ->name('record.save')
    ->middleware('web');
//Route::redirect('/', 'api/');

Route::post('/upload-chunk', [UploadController::class, 'handleChunkedUpload'])->name('upload.chunk');
Route::post('/complete-upload', [UploadController::class, 'completeChunkedUpload'])->name('complete.upload');
