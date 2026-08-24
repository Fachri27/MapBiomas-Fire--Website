<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('infographic', function (Blueprint $table) {
            // Bulan data yang digambarkan infografis, format 'YYYY-MM'.
            // Terpisah dari `publishdate` karena infografis Juli biasanya
            // baru terbit Agustus. Nullable: entri lama jatuh ke bulan
            // publishdate-nya, jadi tak perlu backfill.
            $table->string('period', 7)->nullable()->after('publishdate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('infographic', function (Blueprint $table) {
            $table->dropColumn('period');
        });
    }
};
