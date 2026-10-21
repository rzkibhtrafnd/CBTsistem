<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ExamSessionStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_id')->constrained('tests')->cascadeOnDelete();
            
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('status', ExamSessionStatus::values())->default(ExamSessionStatus::ONGOING->value)->index();
            $table->decimal('total_score', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'event_id', 'test_id']);
            $table->index(['event_id', 'test_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};
