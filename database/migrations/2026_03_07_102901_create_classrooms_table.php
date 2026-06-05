<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('classroom_category_id')
                ->constrained('classroom_categories')
                ->nullOnDelete();

            $table->foreignId('building_id')
                ->constrained('buildings')
                ->nullOnDelete();

            $table->string('room', 50)->unique();
            $table->string('description', 255);
            $table->string('equipment', 255);
            $table->integer('capacity');
            $table->string('google_calendar_id', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {

            $table->dropForeign(['classroom_category_id']);
            $table->dropForeign(['building_id']);

            $table->dropColumn(['classroom_category_id', 'building_id']);
        });
    }
};
