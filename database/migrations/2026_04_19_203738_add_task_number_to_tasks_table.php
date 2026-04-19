<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedInteger('task_number')->nullable()->default(null)->after('project_id');
        });

        // Back-fill existing tasks: number them per project ordered by created_at
        $projects = DB::table('tasks')->select('project_id')->distinct()->pluck('project_id');
        foreach ($projects as $projectId) {
            $tasks = DB::table('tasks')
                ->where('project_id', $projectId)
                ->orderBy('created_at')
                ->pluck('id');
            foreach ($tasks as $i => $id) {
                DB::table('tasks')->where('id', $id)->update(['task_number' => $i + 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('task_number');
        });
    }
};
