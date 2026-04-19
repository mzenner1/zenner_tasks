<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('attachable_type'); // Polymorphic: App\Models\Task or App\Models\Comment
            $table->ulid('attachable_id');     // Polymorphic ID (ULID)
            $table->ulid('user_id');           // Uploader
            $table->string('filename');        // Original file name
            $table->string('path');            // Storage path
            $table->string('mime_type');
            $table->unsignedBigInteger('size'); // Size in bytes
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
