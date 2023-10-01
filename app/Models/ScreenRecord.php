<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreenRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_title',
        'video_description',
        'video_url',
        'video_path',
        'video_thumbnail',
        'video_name',
        'video_size',
        'video_transcription',
        'video_length',
    ];

    protected $hidden = [
        'updated_at',
    ];
}
