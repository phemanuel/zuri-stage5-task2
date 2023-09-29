<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function handleChunkedUpload(Request $request)
    {
        $file = $request->file('file');
        $chunkNumber = $request->input('dzchunkindex');
        $uniqueIdentifier = $request->input('dzuuid');
        $uploadPath = 'uploads/' . $uniqueIdentifier;

        // Store the uploaded chunk in the specified directory
        $file->storeAs($uploadPath, 'chunk_' . $chunkNumber);

        return response()->json(['message' => 'Chunk uploaded successfully']);
    }

    public function completeChunkedUpload(Request $request)
    {
        $uniqueIdentifier = $request->input('dzuuid');
        $uploadPath = 'uploads/' . $uniqueIdentifier;

        $chunks = Storage::files($uploadPath);
        $chunks = collect($chunks)->sort();

        $finalFilename = 'uploaded_' . Str::random(10); // Generate a unique filename
        $filePath = 'uploads/' . $finalFilename; // Specify the final file path

        // Concatenate and store the chunks as the final file
        Storage::put($filePath, '');
        foreach ($chunks as $chunk) {
            Storage::append($filePath, Storage::get($chunk));
        }

        // Delete the chunked files
        Storage::deleteDirectory($uploadPath);

        return response()->json(['message' => 'File upload completed']);
    }
}
