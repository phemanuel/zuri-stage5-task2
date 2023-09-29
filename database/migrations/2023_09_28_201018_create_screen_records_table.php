<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('screen_records', function (Blueprint $table) {
            $table->id();
            $table->string('video_title');
            $table->string('video_description')->nullable();
            $table->string('video_name');
            $table->string('video_size');
            $table->string('video_url');
            $table->string('video_thumbnail')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screen_records');
    }
};
