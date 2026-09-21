<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedInteger('check_in_distance')->nullable()->after('check_out_lng');
            $table->unsignedInteger('check_out_distance')->nullable()->after('check_in_distance');
            $table->index(['date', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['date', 'employee_id']);
            $table->dropColumn(['check_in_distance', 'check_out_distance']);
        });
    }
};