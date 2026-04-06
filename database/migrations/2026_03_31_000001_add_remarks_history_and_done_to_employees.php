<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add remarks_done flag to employees
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('remarks_done')->default(false)->after('remarks');
        });

        // Create a dedicated remarks history table
        Schema::create('remarks_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->text('remark');
            $table->boolean('is_done')->default(false);
            $table->timestamp('done_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remarks_history');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('remarks_done');
        });
    }
};
