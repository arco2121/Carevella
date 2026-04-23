<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabella USERS (Includiamo i campi dei tuoi compagni + supporto Laravel)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username'); // Richiesto dai tuoi compagni
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['paziente', 'medico', 'famiglia'])->default('patient');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 2. Tabella MEDICINES
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // 3. Tabella DATA (Punto 2: Temp e Hum insieme + MAC Address per ESP32)
        Schema::create('data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->string('device_mac', 17)->nullable();
            $table->float('temperature')->nullable();
            $table->float('humidity')->nullable();
            $table->boolean('motion_detected')->default(false); // Sensore PIR
            $table->timestamps();
        });

        // 4. Tabella PRESCRIPTIONS (Terapie)
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained('medicines')->onDelete('cascade');
            $table->integer('step');
            $table->integer('day');
            $table->time('scheduled_time'); // Ora esatta per il servo motore
            $table->integer('amount')->default(1);
            $table->timestamps();
        });

        // 5. Tabella DISPENSER_LOGS (Punto 5: Conferma erogazione fisica)
        Schema::create('dispenser_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->onDelete('cascade');
            $table->timestamp('dispatched_at');
            $table->enum('status', ['success', 'error', 'refilled'])->default('success');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispenser_logs');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('data');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
