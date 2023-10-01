<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\ScreenRecord;
use Illuminate\Http\Request;
use App\Http\Requests\ScreenRecordRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
//use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use GuzzleHttp\Client;
//use FFMpeg\FFMpeg;
use getID3;

class ScreenRecordController extends Controller
{
    //
    public function chunkUpload(Request $request)
    {
        $file = $request->file('file'); // Get the uploaded chunk file
        $chunkNumber = $request->input('dzchunkindex'); // Get the chunk index
    
        $uniqueIdentifier = $request->input('dzuuid'); // Get a unique identifier for the upload (e.g., session ID)
        $uploadPath = 'uploads/' . $uniqueIdentifier; // Path to store chunked files
    
        // Store the uploaded chunk in the specified directory
        $file->storeAs($uploadPath, 'chunk_' . $chunkNumber);
    
        return response()->json(['message' => 'Chunk uploaded successfully']);
    }
    
    public function completeUpload(Request $request)
    {
        $uniqueIdentifier = $request->input('dzuuid');
        $uploadPath = 'uploads/' . $uniqueIdentifier;

        $chunks = Storage::files($uploadPath);
        $chunks = collect($chunks)->sort(); // Sort the chunks in order

        $filePath = 'uploads/' . $request->input('filename'); // Specify the final file path
        
        // Concatenate and store the chunks as the final file
        File::put($filePath, '');
        foreach ($chunks as $chunk) {
            File::append($filePath, File::get($chunk));
    }

    // Delete the chunked files
    Storage::deleteDirectory($uploadPath);

    return response()->json(['message' => 'File upload completed']);

    }

    public function transcribeVideo($id)
    {
        // Fetch the record from the database based on the provided ID
        $screenRecording = ScreenRecord::find($id);

        if (!$screenRecording) {
            // Handle the case where the record with the provided ID is not found
            return response()->json([
                'error' => 'Screen recording not found.',
                'statusCode' => 404,
            ]);
        }

        // Now, you can access all the data associated with this record
        $videoId = $screenRecording->id;
        $videoTitle = $screenRecording->video_title;
        $videoDescription = $screenRecording->video_description;
        $videoUrl = $screenRecording->video_url;
        $videoName = $screenRecording->video_name;
        $videSize = $screenRecording->video_size;

        // Extract audio from the video (you may need a package like FFmpeg)
        $media = FFMpeg::fromDisk('local')
                ->open('app/public/uploads' . $videoUrl)
                ->export()
                ->inFormat(new \FFMpeg\Format\Audio\MP3)
                ->save('app/public/' . $videoName . '.mp3');
        // Define the path to the video file
        $videoPath = $videoUrl;        
        $temporaryAudioPath = storage_path('app/public/'.$videoName . '.mp3');
        // Use FFmpeg to extract audio from the video
        $ffmpegCommand = "ffmpeg -i $videoPath -vn -ar 44100 -ac 2 -ab 192k -f wav $temporaryAudioPath";
        shell_exec($ffmpegCommand);
        // Save the audio as a temporary file
        

        // Call the Whisper ASR API to transcribe the audio
        $whisperApiKey = config('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');
        $client = new Client();
        $response = $client->post('https://whisper-api-url/transcribe', [
            'headers' => [
                'Authorization' => 'Bearer ' . $whisperApiKey,
            ],
            'multipart' => [
                [
                    'name' => 'audio',
                    'contents' => fopen($temporaryAudioPath, 'r'),
                ],
            ],
        ]);

        // Process the transcription response
        $transcription = json_decode($response->getBody()->getContents());

        // Clean up temporary files
        unlink($temporaryAudioPath);
        
        $data = [
            'video_id' => $videoId,
            'video_url' => $videoUrl,
            'video_name' => $videoName,
            'video_description' => $videoDescription,
            'video_size' => $videoSize,
        ];

        return response()->json([
            'data' => $data,
            'transcriptions' => $transcription,
        ]);
    }


    public function screenRecordSave(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'video_title' => 'required|string|max:255',
                'video_description' => 'nullable|string',
                'file' => 'required|mimes:mp4,avi,mov,wmv,mkv|max:20000', //-- 20MB
            ]);

            // Get the uploaded file
            $file = $request->file('file');

            // Set the video_title (file name without extension)
            $videoTitle = str_replace(' ', '_', $validatedData['video_title']);

            // Extract the original file extension
            $fileExtension = $file->getClientOriginalExtension();

            // Generate a timestamp to make the video name unique
            $timestamp = time();

            // Set the video_name with the original extension
            $videoNamePath = $timestamp . '_' . $videoTitle . '.' . $fileExtension;

            // Define the base URL
            $baseUrl = config('app.url');

            // Set the storage path to the public/uploads directory
            $storagePath = 'public/uploads/';

            // Get the size of the uploaded video
            $video_size = $file->getSize();

            // Store the video file in the specified storage path
            $file->storeAs($storagePath, $videoNamePath);

            $path = Storage::path($storagePath . $videoNamePath);

            $publicVideoUrl = asset('storage/uploads/' . $videoNamePath);
            
            // Initialize GetID3
            $getID3 = new GetId3();

            // Analyze the video file
            $fileInfo = $getID3->analyze($path);            
            
            // Get the video duration (in seconds)
            $duration = $fileInfo['playtime_seconds'];

            // You can convert the duration to minutes if needed
            $video_length = $fileInfo['playtime_string'];

            // Save the video recording to the database
            $screenRecording = ScreenRecord::create([
                'video_title' => $validatedData['video_title'],
                'video_description' => $validatedData['video_description'],
                'video_name' => $videoNamePath, // Store the file name with extension
                'video_size' => $video_size,
                'video_url' => $publicVideoUrl,
                'video_length' => $video_length,
                'video_path' => $path,
            ]);

            // Respond with a success message if upload successful
            return response()->json([
                'message' => 'Screen recording saved successfully',
                'statusCode' => 201,
                'data' => $screenRecording,
                
            ]);
        } catch (\Exception $e) {
            // Log the error message
            Log::error($e->getMessage());

            // Respond with an error message if upload not successful
            return response()->json([
                'error' => 'An error occurred while saving the video recording.',
                'statusCode' => 500,
            ]);
        }
    }


    //---Get all screen recordings----
    public function showScreenRecord()
    {
        try {
            // Retrieve all video records from the database
            $screenRecordings = ScreenRecord::all();
    
            // Return the video records as a JSON response
            // return response()->json([
            //     'data' => $screenRecordings,
            //     'statusCode' => 200,
            // ]);
            return view('video-recording', compact('screenRecordings'));
        } catch (\Exception $e) {
            // Log the error message for debugging purposes
            Log::error($e->getMessage());
    
            return response()->json([
                'error' => 'An error occurred while retrieving the video records.',
                'statusCode' => 500,
            ]);
        }
    }

    //------Get a screen recording by id------
    public function showScreenRecordId($id)
    {
        try {
            $screenRecording = ScreenRecord::find($id);
    
            // Check if the screen recording with the id is available
            if (!$screenRecording) {
                return response()->json([
                    'message' => 'Screen recording not found',
                    'statusCode' => 404,
                ]);
            }
    
            // If available, return the screen recording
            return response()->json([
                'data' => $screenRecording,
                'statusCode' => 200,
            ]);
        } catch (\Exception $e) {
            // Log the error message for debugging purposes
            Log::error($e->getMessage());
    
            return response()->json([
                'error' => 'An error occurred while retrieving the screen recording.',
                'statusCode' => 500,
            ]);
        }
    
    }

    public function deleteScreenRecording($id)
    {
        try {
            // Find the screen recording by ID
            $screenRecording = ScreenRecord::find($id);

            // Check if the screen recording exists
            if (!$screenRecording) {
                return response()->json([
                    'message' => 'Screen recording not found',
                    'statusCode' => 404,
                ]);
            }

            // Get the full path to the file from the database
            $filePath = $screenRecording->video_url;
            // Log the file path for debugging
            Log::info('File Path: ' . $filePath);

            // Use the Storage facade to delete the file
            if (Storage::disk('public')->delete($filePath)) {
                // Delete the screen recording record from the database
                $screenRecording->delete();

                return response()->json([
                    'message' => 'Screen recording deleted successfully',
                    'statusCode' => 200,
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to delete the file from storage',
                    'statusCode' => 500,
                ]);
            }
        } catch (\Exception $e) {
            // Log the error message for debugging purposes
            Log::error($e->getMessage());

            return response()->json([
                'error' => 'An error occurred while deleting the screen recording.',
                'statusCode' => 500,
            ]);
        }

    }



}
