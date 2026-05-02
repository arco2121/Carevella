<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('prescription_id')->constrained('prescriptions')->onDelete('cascade');
            $table->date('date');
            $table->boolean('taken')->default(false);
            $table->timestamp('taken_at')->nullable();
            $table->timestamps();

            $table->unique(['patient_id', 'prescription_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_logs');
    }
};
