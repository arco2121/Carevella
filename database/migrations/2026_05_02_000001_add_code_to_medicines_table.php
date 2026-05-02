<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('code', 20)->nullable()->after('id');
        });

        DB::table('medicines')->get()->each(function ($med) {
            $base = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $med->name), 0, 6));
            $code = $base . str_pad($med->id, 3, '0', STR_PAD_LEFT);
            DB::table('medicines')->where('id', $med->id)->update(['code' => $code]);
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->string('code', 20)->nullable(false)->change();
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
