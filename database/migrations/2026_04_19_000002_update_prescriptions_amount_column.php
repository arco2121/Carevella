<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            // Modifica amount da integer a decimal per supportare dosi tipo 0.5
            $table->decimal('amount', 5, 2)->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->integer('amount')->default(1)->change();
        });
    }
};
