<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_correction_request_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_request_id')
                ->constrained(
                    table: 'attendance_correction_requests',
                    indexName: 'acr_breaks_request_id_fk'
                )
                ->cascadeOnDelete();
            $table->dateTime('requested_break_start');
            $table->dateTime('requested_break_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_correction_request_breaks');
    }
};
