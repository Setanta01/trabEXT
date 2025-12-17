<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_game_modes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('creator_name')->default('Anônimo');
            $table->timestamps();
        });

        Schema::create('custom_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_mode_id')->constrained('custom_game_modes')->onDelete('cascade');
            $table->text('question');
            $table->string('correct_answer');
            $table->string('wrong_answer_1');
            $table->string('wrong_answer_2');
            $table->string('wrong_answer_3');
            $table->string('city_name')->nullable(); // null = qualquer cidade
            $table->decimal('city_lat', 10, 7)->nullable();
            $table->decimal('city_lng', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_questions');
        Schema::dropIfExists('custom_game_modes');
    }
};