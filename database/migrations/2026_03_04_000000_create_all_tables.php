<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ─── Users ──────────────────────────────────────────────────
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role')->default('Admin');
            $table->string('profile_image')->nullable();
            $table->string('photo')->nullable();
            $table->string('remember_token')->nullable();
            $table->string('otp_code')->nullable();
            $table->dateTime('otp_expiry')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // ─── Employees ─────────────────────────────────────────────
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('emp_name');
            $table->string('emp_number')->unique();
            $table->string('station')->default('');
            $table->string('last_name')->default('');
            $table->string('first_name')->default('');
            $table->string('middle_name')->default('');
            $table->string('status')->default('ACTIVE');
            $table->string('official_time')->default('8:00 AM - 5:00 PM');
            $table->decimal('without_pay', 8, 2)->default(0);
            $table->integer('tardy')->default(0);
            $table->integer('tardy_minutes')->default(0);
            $table->text('tardiness_dates')->default('');
            $table->string('gender')->default('Male');
        });

        // ─── Daily Attendance ───────────────────────────────────────
        Schema::create('daily_attendance', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id');
            $table->string('year_month');
            $table->integer('day');
            $table->string('status')->default('absent');
            $table->primary(['employee_id', 'year_month', 'day']);
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // ─── Absence Reasons ────────────────────────────────────────
        Schema::create('absence_reasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('year_month');
            $table->integer('day');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['employee_id', 'year_month', 'day']);
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // ─── Absences (Legacy) ──────────────────────────────────────
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('absence_date');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // ─── Late Minutes ───────────────────────────────────────────
        Schema::create('late_minutes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('year_month');
            $table->integer('day');
            $table->integer('minutes');
            $table->unique(['employee_id', 'year_month', 'day']);
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // ─── Undertime Minutes ──────────────────────────────────────
        Schema::create('undertime_minutes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('year_month');
            $table->integer('day');
            $table->integer('minutes');
            $table->unique(['employee_id', 'year_month', 'day']);
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // ─── Special Days (Holidays) ────────────────────────────────
        Schema::create('special_days', function (Blueprint $table) {
            $table->id();
            $table->string('date_str')->unique();
            $table->text('reason')->nullable();
        });

        // ─── Forms ──────────────────────────────────────────────────
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('filename')->nullable();
            $table->string('filesize')->nullable();
            $table->longText('html_content')->nullable();
            $table->text('embed_url')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
        });

        // ─── Audit Logs ─────────────────────────────────────────────
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('username')->nullable();
            $table->string('action')->nullable();
            $table->string('module')->nullable();
            $table->text('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // ─── Activity Logs ──────────────────────────────────────────
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('username')->nullable();
            $table->string('action')->nullable();
            $table->text('details')->nullable();
            $table->timestamp('timestamp')->useCurrent();
        });

        // ─── Login Logs ─────────────────────────────────────────────
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('role')->nullable();
            $table->timestamp('login_time')->useCurrent();
            $table->string('ip_address')->nullable();
            $table->timestamp('logout_time')->nullable();
        });

        // ─── Correction Requests ────────────────────────────────────
        Schema::create('correction_requests', function (Blueprint $table) {
            $table->id();
            $table->string('submitter_username');
            $table->string('emp_number')->nullable();
            $table->string('req_date');
            $table->string('type');
            $table->text('reason');
            $table->string('status')->default('Pending');
            $table->string('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // ─── Monthly DAW ────────────────────────────────────────────
        Schema::create('monthly_daw', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id');
            $table->string('year_month');
            $table->string('daw_id')->nullable();
            $table->string('daw_status')->default('absent');
            $table->primary(['employee_id', 'year_month']);
        });

        // ─── Notes ──────────────────────────────────────────────────
        Schema::create('notes', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id');
            $table->string('year_month');
            $table->integer('day');
            $table->text('note')->nullable();
            $table->primary(['employee_id', 'year_month', 'day']);
        });

        // ─── QR Tokens ─────────────────────────────────────────────
        Schema::create('qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->integer('created_at');
            $table->integer('expires_at');
            $table->boolean('used')->default(false);
        });

        // ─── Settings ───────────────────────────────────────────────
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('qr_tokens');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('monthly_daw');
        Schema::dropIfExists('correction_requests');
        Schema::dropIfExists('login_logs');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('forms');
        Schema::dropIfExists('special_days');
        Schema::dropIfExists('undertime_minutes');
        Schema::dropIfExists('late_minutes');
        Schema::dropIfExists('absences');
        Schema::dropIfExists('absence_reasons');
        Schema::dropIfExists('daily_attendance');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('users');
    }
};
