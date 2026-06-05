<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('purpose', 255);
            $table->string('equipment', 255)->nullable();
            $table->boolean('is_tech_support');
            $table->string('user_comment')->nullable();
            $table->string('admin_comment')->nullable();
            $table->enum('status', ['pending','approved','rejected', 'cancelled'])->default('pending');
            $table->string('google_event_id', 200)->nullable();
            $table->string('vk_link')->nullable();
            $table->string('vk_user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('faculty')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
