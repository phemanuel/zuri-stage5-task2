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
                'file' => 'required|mimes:mp4,avi,mov,wmv|max:100000000',
            ]);

            // Get the uploaded file
            $file = $request->file('file');

            // Set the video_title (file name without extension)
            $videoUrl = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            // Generate a timestamp to make the video name unique
            $timestamp = time();

            // Set the video_name (file name with extension)
            $videoName = $timestamp . '_' . $file->getClientOriginalName();

            // Get the storage path where the file will be saved
            $storagePath = 'uploads'; // You can customize this path as needed

            // Set the full path to where the file is stored in your project
            $fullFilePath = $storagePath . '/' . $videoName;

            //----Get video size-----
            $video_size = $file->getSize();

            //------Store the video-----
            $file->storeAs($storagePath, $videoName);

            // Create a clickable link for the video URL
            $videoLink = '<a href="' . public_path($fullFilePath) . '" target="_blank">View Video</a>';
            
            // Save the video recording to the database
            $screenRecording = ScreenRecord::create([
                'video_title' => $validatedData['video_title'],
                'video_description' => $validatedData['video_description'],
                'video_name' => $videoName,
                'video_size' => $video_size,
                'video_url' => public_path($fullFilePath),//-- full file path without hyperlink
                //'video_url' => $videoLink, //---file path with hyperlink
            ]);
            
            // Respond with a success message if upload successful
            return response()->json([
                'message' => 'Screen recording saved successfully',                
                'statusCode' => 201,
                'data' => $screenRecording
            ]);
        } catch (\Exception $e) {
            // Log the error message 
            Log::error($e->getMessage());
            // Respond with an error message if upload not successful
            return response()->json([
                'error' => 'An error occurred while saving the video recording.',
                'statusCode' => 500
            ]);
        }
    }


    public function showScreenRecord()
    {
        $screenRecordings = ScreenRecord::all();

    return response()->json($screenRecordings);
    }

    public function showScreenRecordId($id)
    {
        $screenRecording = ScreenRecord::find($id);

        if (!$screenRecording) {
            return response()->json(['message' => 'Screen recording not found'], 404);
        }

        return response()->json($screenRecording);
    }
}
