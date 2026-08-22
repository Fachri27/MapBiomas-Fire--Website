<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now('Asia/Jakarta');

        $faqs = [
            [
                'questionID' => 'Apa itu pemantauan bulanan MapBiomas Fire?',
                'questionEN' => 'What is MapBiomas Fire monthly monitoring?',
                'answerID' => '<p>Pemantauan bulanan memetakan area terbakar di Indonesia setiap bulan menggunakan citra satelit dan algoritma deep learning.</p>',
                'answerEN' => '<p>Monthly monitoring maps burned areas in Indonesia every month using satellite imagery and deep learning algorithms.</p>',
            ],
            [
                'questionID' => 'Kapan data bulanan diperbarui?',
                'questionEN' => 'When is the monthly data updated?',
                'answerID' => '<p>Data bulanan diperbarui pada minggu pertama setiap bulan untuk periode observasi sebelumnya.</p>',
                'answerEN' => '<p>Monthly data is updated in the first week of each month for the previous observation period.</p>',
            ],
            [
                'questionID' => 'Apa itu rekap tahunan MapBiomas Fire?',
                'questionEN' => 'What is the MapBiomas Fire annual report?',
                'answerID' => '<p>Rekap tahunan merangkum luas dan distribusi area terbakar sepanjang satu tahun kalender, dimulai dari Koleksi 1 tahun 2000.</p>',
                'answerEN' => '<p>The annual report summarizes the extent and distribution of burned areas throughout a calendar year, starting from Collection 1 in 2000.</p>',
            ],
            [
                'questionID' => 'Di mana saya dapat mengunduh laporan tahunan?',
                'questionEN' => 'Where can I download the annual report?',
                'answerID' => '<p>Laporan tahunan tersedia pada halaman Downloads beserta dokumen metodologi ATBD.</p>',
                'answerEN' => '<p>Annual reports are available on the Downloads page along with the ATBD methodology documents.</p>',
            ],
        ];

        foreach ($faqs as $faq) {
            DB::table('faq')->updateOrInsert(
                ['questionID' => $faq['questionID']],
                array_merge($faq, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
