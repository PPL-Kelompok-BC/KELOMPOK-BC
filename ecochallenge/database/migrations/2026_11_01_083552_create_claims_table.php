<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('challenge_id')
                ->constrained('challenges')
                ->restrictOnDelete();

            $table->string('proof_path')->nullable();
            $table->string('photo_hash', 64)->nullable()->unique();
            $table->timestamp('photo_taken_at')->nullable();
            $table->boolean('needs_review')->default(true);

            $table->date('claim_date');
            $table->string('status')->default('pending');

            $table->unsignedInteger('points_awarded')->default(0);
            $table->unsignedInteger('co2e_awarded_gram')->default(0);

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->timestamps();

            $table->unique(
                ['user_id', 'challenge_id', 'claim_date'],
                'claims_user_challenge_date_unique'
            );

            $table->index(['user_id', 'claim_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};