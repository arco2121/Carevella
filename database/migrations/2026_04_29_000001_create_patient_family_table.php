<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_family', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('family_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['patient_id', 'family_id']); // no duplicati
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_family');
    }
};
