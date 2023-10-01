<?php
use App\Http\Controllers\ScreenRecordController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;

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

// Route::post('/upload-chunk', [ScreenRecordController::class, 'chunkUpload'])->name('upload.chunk');
// Route::post('/complete-upload', [ScreenRecordController::class, 'completeUpload'])->name('complete.upload');
Route::post('/screen-recording', [ScreenRecordController::class, 'screenRecordSave'])
    ->name('recording.save')
    ->middleware('web');
Route::get('/view-recording', [ScreenRecordController::class, 'showScreenRecord'])
->name('recording.view');
Route::get('/screen-recording/{id}', [ScreenRecordController::class, 'showScreenRecordId'])
->name('recording.view');
Route::delete('/screen-recording/{id}', [ScreenRecordController::class, 'deleteScreenRecording'])
->name('recording.delete');
Route::get('/test-guzzle', function () {
    $client = new Client();
    $response = $client->get('https://kingsconsult.com.ng/kbc');
    $contents = $response->getBody()->getContents();
    return $contents;
});
Route::get('/transcribe-video/{id}', [ScreenRecordController::class, 'transcribeVideo'])
->name('recording.transcribe');
//Route::redirect('/', 'api/');

// Route::post('/upload-chunk', [UploadController::class, 'handleChunkedUpload'])->name('upload.chunk');
// Route::post('/complete-upload', [UploadController::class, 'completeChunkedUpload'])->name('complete.upload');
