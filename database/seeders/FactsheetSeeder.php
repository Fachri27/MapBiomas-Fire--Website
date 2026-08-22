<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Fact sheet bukan lagi halaman CMS satu baris melainkan daftar tersendiri,
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
                'titleID' => 'Fact Sheet Pemantauan Bulanan',
                'titleEN' => 'Monthly Monitoring Fact Sheet',
            ],
            [
                'category' => 'annual',
                'titleID' => 'Fact Sheet Pemantauan Tahunan',
                'titleEN' => 'Annual Monitoring Fact Sheet',
            ],
        ];

        foreach ($sheets as $sheet) {
            DB::table('factsheet')->updateOrInsert(
                ['category' => $sheet['category']],
                array_merge($sheet, [
                    'descriptionID' => 'Untuk mengunduh fact sheet MapBiomas Indonesia koleksi 1, gunakan tautan berikut',
                    'descriptionEN' => 'To download the MapBiomas Indonesia fact sheet collection 1, you can use the following',
                    // Placeholder; ganti lewat CMS begitu berkasnya tersedia.
                    'link' => '#',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}
