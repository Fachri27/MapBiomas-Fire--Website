<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now('Asia/Jakarta');

        $items = [
            [
                'category' => 'news',
                'publishdate' => Carbon::now('Asia/Jakarta')->subDays(5)->format('Y-m-d H:i:s'),
                'titleID' => 'Pembaruan data area terbakar bulan lalu telah dirilis',
                'titleEN' => 'Last month burned area data update has been released',
                'descriptionID' => '<p>Data pemantauan bulanan terbaru kini tersedia pada platform.</p>',
                'descriptionEN' => '<p>The latest monthly monitoring data is now available on the platform.</p>',
                'contentID' => '<p>Data pemantauan bulanan terbaru kini tersedia pada platform. Pembaruan mencakup peta area terbakar beserta statistik per region.</p>',
                'contentEN' => '<p>The latest monthly monitoring data is now available on the platform. The update includes burned area maps and per-region statistics.</p>',
            ],
            [
                'category' => 'news',
                'publishdate' => Carbon::now('Asia/Jakarta')->subDays(12)->format('Y-m-d H:i:s'),
                'titleID' => 'Kolaborasi pemantauan karhutla diperluas ke wilayah timur Indonesia',
                'titleEN' => 'Forest and land fire monitoring collaboration expands to eastern Indonesia',
                'descriptionID' => '<p>Jaringan organisasi mitra menambah cakupan wilayah pemantauan.</p>',
                'descriptionEN' => '<p>Partner organization networks expand the monitoring coverage area.</p>',
                'contentID' => '<p>Jaringan organisasi mitra menambah cakupan wilayah pemantauan di kawasan timur Indonesia, termasuk Kepulauan Maluku dan Tanah Papua.</p>',
                'contentEN' => '<p>Partner organization networks expand monitoring coverage in eastern Indonesia, including the Maluku Islands and Tanah Papua.</p>',
            ],
            [
                'category' => 'event',
                'publishdate' => Carbon::now('Asia/Jakarta')->addDays(14)->format('Y-m-d H:i:s'),
                'titleID' => 'Lokakarya nasional pemetaan area terbakar 2026',
                'titleEN' => 'National burned area mapping workshop 2026',
                'descriptionID' => '<p>Lokakarya membahas hasil pemantauan terbaru bersama para pakar dan mitra.</p>',
                'descriptionEN' => '<p>The workshop discusses the latest monitoring results with experts and partners.</p>',
                'contentID' => '<p>Lokakarya membahas hasil pemantauan terbaru bersama para pakar, peneliti, dan organisasi mitra di Jakarta.</p>',
                'contentEN' => '<p>The workshop discusses the latest monitoring results with experts, researchers, and partner organizations in Jakarta.</p>',
            ],
        ];

        foreach ($items as $item) {
            DB::table('news')->updateOrInsert(
                ['slug' => Str::slug($item['titleEN'])],
                array_merge($item, [
                    'img' => 'sample-news.jpg',
                    'slug' => Str::slug($item['titleEN']),
                    'status' => '1',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}
