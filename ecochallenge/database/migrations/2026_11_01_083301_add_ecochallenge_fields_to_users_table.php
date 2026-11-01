<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique();
            $table->string('role')->default('mahasiswa');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('total_points')->default(0);

            $table->index('total_points');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['total_points']);
            $table->dropUnique(['username']);

            $table->dropColumn([
                'username',
                'role',
                'is_active',
                'total_points',
            ]);
        });
    }
};