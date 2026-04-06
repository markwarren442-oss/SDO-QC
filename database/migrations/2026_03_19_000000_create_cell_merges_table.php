<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cell_merges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('year_month', 7);   // e.g. 2026-03
            $table->unsignedTinyInteger('start_day');
            $table->unsignedTinyInteger('end_day');
            $table->string('label', 10);       // e.g. A, /, HOL, SL, OB…
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'year_month', 'start_day'], 'unique_merge');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cell_merges');
    }
};
