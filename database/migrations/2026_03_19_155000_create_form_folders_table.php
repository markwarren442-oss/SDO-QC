<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('form_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('parent_id')->references('id')->on('form_folders')->onDelete('cascade');
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->unsignedBigInteger('folder_id')->nullable()->after('id');
            $table->foreign('folder_id')->references('id')->on('form_folders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropColumn('folder_id');
        });
        Schema::dropIfExists('form_folders');
    }
};
