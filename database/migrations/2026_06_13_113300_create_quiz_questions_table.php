<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->longText('question_text');
            $table->string('question_type')->default('multiple_choice'); // multiple_choice, true_false, short_answer
            $table->json('options')->nullable();
            $table->longText('correct_answer')->nullable();
            $table->decimal('marks', 5, 2)->default(1);
            $table->longText('explanation')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('quiz_questions'); }
};