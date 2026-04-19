<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('project_id');
            $table->unsignedBigInteger('status_id');
            $table->ulid('created_by');
            $table->string('title');
            $table->longText('description')->nullable(); // Markdown supported
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->date('due_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('status_id')->references('id')->on('statuses')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
