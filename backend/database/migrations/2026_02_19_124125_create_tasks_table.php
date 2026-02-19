<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('creator_id')->index();
            $table->unsignedBigInteger('assignee_id')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default(TaskStatus::Todo->value);
            $table->string('priority')->default(TaskPriority::Medium->value);
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE tasks ADD COLUMN search_vector tsvector');
        DB::statement('CREATE INDEX tasks_search_vector_gin ON tasks USING gin(search_vector)');
        DB::statement("
            CREATE TRIGGER tasks_search_vector_update
            BEFORE INSERT OR UPDATE ON tasks
            FOR EACH ROW EXECUTE FUNCTION
            tsvector_update_trigger(search_vector, 'pg_catalog.english', title, description)
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS tasks_search_vector_update ON tasks');
        Schema::dropIfExists('tasks');
    }
};
