<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    private function terbitkanNews(array $ubah = []): void
    {
        DB::table('news')->insert(array_merge([
            'publishdate' => now()->format('Y-m-d'),
            'titleID' => 'JUDUL-BERITA',
            'titleEN' => 'NEWS-TITLE',
            'img' => 'a.jpg',
            'descriptionID' => 'DESKRIPSI',
            'category' => 'news',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], $ubah));
    }

    /** @test */
    public function dashboard_kosong_tetap_terbuka(): void
    {
        $this->withSession(['id' => 1])->get('/cms/dashboard')
            ->assertOk()
            ->assertSee('No content yet')
            ->assertSee('No news yet');
    }

    /** @test */
    public function dashboard_menghitung_terbit_draft_dan_tren(): void
    {
        $this->terbitkanNews();
        $this->terbitkanNews(['status' => 0, 'category' => 'event', 'titleID' => 'JUDUL-ACARA']);
        $this->terbitkanNews(['publishdate' => now()->subYear()->format('Y-m-d')]);

        $res = $this->withSession(['id' => 1])->get('/cms/dashboard')->assertOk();

        // 3 news total, 2 terbit / 1 draft; kategori news 2 & event 1.
        $res->assertSee('2 published · 1 draft')
            ->assertSee('JUDUL-ACARA')
            ->assertSee('draft');

        $html = $res->getContent();
        $this->assertMatchesRegularExpression('/text-4xl[^>]*>\s*3\s*</', $html);
        // Bar dipasang lewat style inline, bukan class Tailwind dinamis.
        $this->assertStringContainsString('style="width:', $html);
        $this->assertStringContainsString('style="height:', $html);
        // Hanya bulan yang berisi yang muncul: bulan ini 2 berita (bar penuh)
        // dan bulan setahun lalu 1 berita (setengah).
        $this->assertSame(1, substr_count($html, 'height: 100%'));
        $this->assertSame(1, substr_count($html, 'height: 50%'));
        $this->assertStringContainsString(now()->format('M y'), $html);
    }
}
