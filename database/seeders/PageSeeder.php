<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now('Asia/Jakarta');

        $pages = [
            'pageabout' => [
                'contentID' => '<p>Inisiatif MapBiomas Fire dimulai sejak 2023, bersama sembilan jaringan organisasi masyarakat sipil yang dikoordinasi oleh Auriga Nusantara dan Woods &amp; Wayside International. Peta area terbakar dihasilkan menggunakan teknologi komputasi berbasis machine learning dan deep learning.</p>',
                'contentEN' => '<p>The MapBiomas Fire initiative started in 2023 together with nine civil society networks coordinated by Auriga Nusantara and Woods &amp; Wayside International. Burned area maps are produced using computational technology based on machine learning and deep learning.</p>',
            ],
            'pagetermofuse' => [
                'contentID' => '<p>Seluruh data dan peta pada platform ini disediakan secara terbuka untuk tujuan non-komersial dengan tetap menyertakan atribusi MapBiomas Fire Indonesia.</p>',
                'contentEN' => '<p>All data and maps on this platform are openly provided for non-commercial purposes with proper attribution to MapBiomas Fire Indonesia.</p>',
            ],
            'pagerefrencemap' => [
                'contentID' => '<p>Peta referensi digunakan sebagai pembanding independen dalam validasi hasil klasifikasi area terbakar.</p>',
                'contentEN' => '<p>Reference maps are used as an independent comparison for validating burned area classification results.</p>',
            ],
            'pagedownload' => [
                'contentID' => '<p>Unduh data area terbakar bulanan, rekap tahunan, serta dokumen metodologi melalui tautan resmi platform.</p>',
                'contentEN' => '<p>Download monthly burned area data, annual summaries, and methodology documents through the official platform links.</p>',
            ],
        ];

        foreach ($pages as $table => $page) {
            DB::table($table)->updateOrInsert(
                ['id' => 1],
                array_merge(['name' => 'whoweare'], $page, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        // ATBD punya dua varian konten: monthly dan annual.
        $atbd = [
            'monthly' => [
                'contentID' => '<p>Dokumen ATBD bulanan menjelaskan metodologi pemantauan area terbakar pada periode observasi satu bulan.</p>',
                'contentEN' => '<p>The monthly ATBD document describes the methodology for monitoring burned areas within a one-month observation period.</p>',
            ],
            'annual' => [
                'contentID' => '<p>Dokumen ATBD tahunan menjelaskan metodologi rekapitulasi area terbakar sepanjang satu tahun kalender.</p>',
                'contentEN' => '<p>The annual ATBD document describes the methodology for summarizing burned areas throughout a calendar year.</p>',
            ],
        ];

        foreach ($atbd as $category => $page) {
            DB::table('pageatbd')->updateOrInsert(
                ['category' => $category],
                [
                    'name' => 'atbd-'.$category,
                    'category' => $category,
                    'contentID' => $page['contentID'],
                    'contentEN' => $page['contentEN'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
