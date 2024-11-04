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
        Schema::create('guest_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sudoku_id')->constrained('sudokus')->cascadeOnDelete();
            $table->boolean('finished')->default(false);
            $table->integer('timer_seconds')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_games');
    }
};
