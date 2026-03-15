<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('logged_by')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->string('weather')->nullable();
            $table->decimal('temperature', 5, 1)->nullable();
            $table->string('weather_icon')->nullable();
            $table->text('work_performed');
            $table->unsignedInteger('workers_on_site')->default(0);
            $table->string('equipment_used')->nullable();
            $table->text('delays_issues')->nullable();
            $table->unique(['project_id', 'date']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
