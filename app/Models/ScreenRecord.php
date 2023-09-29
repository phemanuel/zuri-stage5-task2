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
        'video_thumbnail',
        'video_name',
        'video_size',
    ];

    protected $hidden = [
        'updated_at',
    ];
}
