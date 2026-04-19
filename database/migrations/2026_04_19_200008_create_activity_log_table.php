<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->ulid('task_id');
            $table->ulid('user_id')->nullable(); // Null = system event
            $table->string('event');             // e.g. status_changed, assigned, commented
            $table->json('properties')->nullable(); // e.g. {"from": "Open", "to": "In Progress"}
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
