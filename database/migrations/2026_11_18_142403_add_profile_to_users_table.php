<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['warga', 'medis'])->default('warga')->after('email');
            $table->string('health_category')->default('Umum')->after('role');
            $table->string('kelurahan')->default('Sungai Miai')->after('health_category');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'health_category', 'kelurahan']);
        });
    }
};