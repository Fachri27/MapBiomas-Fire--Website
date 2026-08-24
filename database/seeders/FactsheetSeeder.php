<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Factsheet bukan lagi halaman CMS satu baris melainkan daftar tersendiri,
 * jadi seedernya berdiri sendiri seperti NewsSeeder dan InfographicSeeder.
 *
 * `description` berhenti tepat sebelum tautan — kata [Link] beserta titiknya
 * dirakit di view, jadi kalimat ini sengaja tidak diakhiri tanda baca.
 */
class FactsheetSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now('Asia/Jakarta');

        $sheets = [
            [
                'category' => 'monthly',
                'titleID' => 'Factsheet Pemantauan Bulanan',
                'titleEN' => 'Monthly Monitoring Factsheet',
            ],
            [
                'category' => 'annual',
                'titleID' => 'Factsheet Pemantauan Tahunan',
                'titleEN' => 'Annual Monitoring Factsheet',
            ],
        ];

        foreach ($sheets as $sheet) {
            DB::table('factsheet')->updateOrInsert(
                ['category' => $sheet['category']],
                array_merge($sheet, [
                    'descriptionID' => 'Untuk mengunduh factsheet MapBiomas Indonesia koleksi 1, gunakan tautan berikut',
                    'descriptionEN' => 'To download the MapBiomas Indonesia factsheet collection 1, you can use the following',
                    // Placeholder; ganti lewat CMS begitu berkasnya tersedia.
                    'link' => '#',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}
