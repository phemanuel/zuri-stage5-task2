<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\ScreenRecord;
use Illuminate\Http\Request;
use App\Http\Requests\ScreenRecordRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

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

    public function screenRecordSave(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'video_title' => 'required|string|max:255',
                'video_description' => 'nullable|string',
                'file' => 'required|mimes:mp4,avi,mov,wmv,mkv|max:20000',//--20MB
                //'file' => 'required|mimes:mp4,avi,mov,wmv|max:100000000',
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

            //--set a unique video name
            $videoName = $timestamp . '_' . $videoTitle;  
            
            // Define the base URL
            $baseUrl = config('app.url');

            // Set the storage path to the public/uploads directory
            $storagePath = 'public/uploads'; // This will save files in the public directory

            // Set the full path to where the file will be stored
            $fullFilePath =  $storagePath . '/' . $videoNamePath;

            // Get the size of the uploaded video
            $video_size = $file->getSize();

            // Store the video file in the specified storage path
            $file->storeAs($storagePath, $videoNamePath);

            $publicVideoUrl = asset('storage/uploads/' . $videoNamePath);            

            // Save the video recording to the database
            $screenRecording = ScreenRecord::create([
                'video_title' => $validatedData['video_title'],
                'video_description' => $validatedData['video_description'],
                'video_name' => $videoName,
                'video_size' => $video_size,
                'video_url' =>  $publicVideoUrl, // Store the path relative to the public directory
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
