<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Berkas unduhan ikut dua bahasa: satu PDF/tautan untuk edisi Indonesia,
     * satu lagi untuk edisi Inggris. Sebelumnya `link`/`file` dipakai bersama,
     * jadi pembaca EN ikut mengunduh PDF berbahasa Indonesia.
     */
    public function up(): void
    {
        Schema::table('factsheet', function (Blueprint $table) {
            $table->string('linkID')->default('')->after('descriptionEN');
            $table->string('linkEN')->default('')->after('linkID');
            $table->string('fileID')->nullable()->after('linkEN');
            $table->string('fileEN')->nullable()->after('fileID');
        });

        // Entri lama bahasa-netral: pakai berkas yang sama untuk kedua bahasa
        // sampai editor mengunggah versi terjemahannya lewat CMS.
        DB::table('factsheet')->update([
            'linkID' => DB::raw('link'),
            'linkEN' => DB::raw('link'),
            'fileID' => DB::raw('file'),
            'fileEN' => DB::raw('file'),
        ]);

        Schema::table('factsheet', function (Blueprint $table) {
            $table->dropColumn(['link', 'file']);
        });
    }

    public function down(): void
    {
        Schema::table('factsheet', function (Blueprint $table) {
            $table->string('link')->default('')->after('descriptionEN');
            $table->string('file')->nullable()->after('link');
        });

        // Edisi Indonesia yang dipertahankan; versi EN hilang saat mundur.
        DB::table('factsheet')->update([
            'link' => DB::raw('linkID'),
            'file' => DB::raw('fileID'),
        ]);

        Schema::table('factsheet', function (Blueprint $table) {
            $table->dropColumn(['linkID', 'linkEN', 'fileID', 'fileEN']);
        });
    }
};
