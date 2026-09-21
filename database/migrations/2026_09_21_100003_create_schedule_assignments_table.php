<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_schedule_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1=Senin ... 7=Minggu
            $table->timestamps();
            $table->unique(['employee_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_assignments');
    }
};