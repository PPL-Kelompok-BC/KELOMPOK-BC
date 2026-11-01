<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streak_freezes', function (Blueprint $table) {
            $table->id();

            // Tanggal gangguan sistem, unik secara global
            $table->date('freeze_date')->unique();

            // Alasan tanggal tersebut ditandai sebagai gangguan sistem
            $table->text('reason');

            // Admin yang menandai tanggal gangguan
            $table->foreignId('marked_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streak_freezes');
    }
};
