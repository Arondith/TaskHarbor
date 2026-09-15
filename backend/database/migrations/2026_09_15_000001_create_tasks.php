<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('tasks', function(Blueprint $table) {
            $table->id();$table->string('title',160);$table->string('priority')->default('medium');$table->string('status')->default('todo');$table->timestamps();
        });
        Schema::create('task_events', function(Blueprint $table) {
            $table->id();$table->foreignId('task_id')->constrained()->cascadeOnDelete();$table->string('action');$table->timestamp('created_at');
        });
    }
    public function down(): void {Schema::dropIfExists('task_events');Schema::dropIfExists('tasks');}
};
