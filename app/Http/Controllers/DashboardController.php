<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function login(){
        $title = 'MapBiomas Fire - Login';
        return view('backends.login', compact('title'));
    }

    public function index(){
        $title = 'MapBiomas Fire - Dashboard';
        $nav = 'dashboard';
        return view('backends.dashboard', array_merge(
            compact('title', 'nav'),
            $this->summary()
        ));
    }

    /**
     * Angka ringkasan untuk dashboard. Tabel yang belum ada (fresh clone,
     * migrasi belum jalan) tidak boleh membuat halaman mati, jadi seluruh
     * blok dibungkus try/catch seperti komponen list lainnya.
     */
    private function summary(){
        $empty = [
            'stats' => [],
            'newsByCategory' => collect(),
            'factsheetByCategory' => collect(),
            'trend' => collect(),
            'recentNews' => collect(),
            'recentInfographic' => collect(),
        ];

        try {
            $news = DB::table('news');
            $newsTotal = (clone $news)->count();
            $newsPublished = (clone $news)->where('status', 1)->count();

            $info = DB::table('infographic');
            $infoTotal = (clone $info)->count();
            $infoPublished = (clone $info)->where('status', 1)->count();

            $stats = [
                [
                    'label' => 'News & Events',
                    'value' => $newsTotal,
                    'note'  => $newsPublished.' published · '.($newsTotal - $newsPublished).' draft',
                    'url'   => url('/cms/listnews'),
                ],
                [
                    'label' => 'Infographics',
                    'value' => $infoTotal,
                    'note'  => $infoPublished.' published · '.($infoTotal - $infoPublished).' draft',
                    'url'   => url('/cms/listinfographic'),
                ],
                [
                    'label' => 'Factsheets',
                    'value' => DB::table('factsheet')->count(),
                    'note'  => 'all editions',
                    'url'   => url('/cms/listfactsheet'),
                ],
                [
                    'label' => 'FAQ',
                    'value' => DB::table('faq')->count(),
                    'note'  => 'questions answered',
                    'url'   => url('/cms/listfaq'),
                ],
            ];

            // publishdate disimpan sebagai varchar 'Y-m-d' (flatpickr), jadi
            // substr aman untuk mengelompokkan per bulan. Yang diambil enam
            // bulan terakhir yang ADA isinya, bukan enam bulan kalender —
            // situs ini kadang berbulan-bulan tanpa post, dan grafik yang
            // selalu nol tidak memberi informasi apa pun.
            $trend = DB::table('news')
                ->selectRaw('substr(publishdate, 1, 7) as ym, COUNT(*) as total')
                ->groupBy('ym')->orderByDesc('ym')->limit(6)->get()
                ->reverse()->values()
                ->map(fn ($row) => [
                    'label' => date('M y', strtotime($row->ym.'-01')),
                    'total' => (int) $row->total,
                ]);

            return [
                'stats' => $stats,
                'newsByCategory' => DB::table('news')
                    ->selectRaw('category, COUNT(*) as total')
                    ->groupBy('category')->orderByDesc('total')->get(),
                'factsheetByCategory' => DB::table('factsheet')
                    ->selectRaw('category, COUNT(*) as total')
                    ->groupBy('category')->orderByDesc('total')->get(),
                'trend' => $trend,
                'recentNews' => DB::table('news')
                    ->select('id', 'titleID', 'category', 'status', 'publishdate')
                    ->orderByDesc('publishdate')->limit(5)->get(),
                'recentInfographic' => DB::table('infographic')
                    ->select('id', 'titleEN', 'status', 'publishdate')
                    ->orderByDesc('publishdate')->limit(5)->get(),
            ];
        } catch (\Throwable $th) {
            return $empty;
        }
    }
}
