<?php

namespace App\Http\Controllers;
use App\Models\ScreenRecord;
use Illuminate\Http\Request;
use App\Http\Requests\ScreenRecordRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScreenRecordController extends Controller
{
    //
    public function screenRecordSave(ScreenRecordRequest $request)
    {
        try {
            // Validation has passed at this point
    
            // Retrieve the validated data
            $validatedData = $request->validated();
    
            // Get the uploaded file
            $file = $request->file('file');
    
            // Set the video_title (file name without extension)
            $videoTitle = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            $newFilename = time() . '_' . $videoTitle;
    
            // Set the video_name (file name with extension)
            $videoName = $file->getClientOriginalName();
    
            // Save the video recording to the database
            $screenRecording = ScreenRecord::create([
                'video_title' => $videoTitle,
                'video_description' => $validatedData['video_description'],
                'video_name' => $videoName,
                'video_size' => $file->getSize(),
                'video_url' => $file->store($newFilename, 'uploads'), // Assuming 'uploads' is your disk name
                'video_thumbnail' => 'thumbnail_url_here', // Set the thumbnail URL as needed
            ]);
    
            // Respond with a success message or the saved video recording
            return response()->json([
                'message' => 'Video recording saved successfully',
                'data' => $screenRecording,
                'statusCode'=> 201
                ]);
        } catch (\Exception $e) {
            // Handle exceptions here, e.g., log the error
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
