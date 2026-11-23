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
    Schema::create('health_screenings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->enum('type', ['nutriscan', 'triase']);
        $table->text('input_summary');
        $table->string('image_path')->nullable();
        $table->enum('risk_level', ['aman', 'waspada', 'darurat']);
        $table->json('ai_result');
        $table->string('referral_code')->nullable()->unique();
        $table->enum('referral_status', ['menunggu', 'diperiksa', 'selesai'])->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_screenings');
    }
};
