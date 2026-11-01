<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');
            $table->text('description');
            $table->string('difficulty');
            $table->unsignedInteger('points')->default(0);
            $table->boolean('is_active')->default(true);

            $table->text('proof_criteria')->nullable();
            $table->unsignedInteger('estimated_co2e_gram')->default(0);
            $table->string('emission_source')->nullable();

            $table->timestamps();

            $table->index(['category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};