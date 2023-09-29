<?php

namespace App\Http\Requests;
use App\Http\Controllers\ScreenRecordController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class ScreenRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'video_title' => 'required|string',
            'video_description' => 'nullable|string',
            // 'video_url' => 'required|mimetypes:video/*|max:100000000',
        ];
    }

  
}
