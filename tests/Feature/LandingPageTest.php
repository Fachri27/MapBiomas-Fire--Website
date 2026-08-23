<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Landing (frontends/landing.blade.php) — halaman yang dilayani rute `index`.
 *
 * Isinya sengaja berupa pemeriksaan perilaku, bukan potongan markup: yang diuji
 * adalah tujuan tautan, penyaringan data, dan teks yang sampai ke pembaca.
 */
class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    /** Berita terbit, di masa lalu, kategori `news`. */
    private function terbitkanBerita(array $ubah = []): int
    {
        return DB::table('news')->insertGetId(array_merge([
            'category' => 'news',
            'publishdate' => Carbon::now('Asia/Jakarta')->subDay()->format('Y-m-d H:i:s'),
            'titleID' => 'Judul berita',
            'titleEN' => 'News title',
            'img' => 'berita.jpg',
            'descriptionID' => 'Ringkasan berita.',
            'descriptionEN' => 'News summary.',
            'contentID' => '<p>Isi.</p>',
            'contentEN' => '<p>Body.</p>',
            'slug' => 'judul-berita',
            'status' => '1',
        ], $ubah));
    }

    private function terbitkanInfografis(array $ubah = []): void
    {
        DB::table('infographic')->insert(array_merge([
            'publishdate' => Carbon::now('Asia/Jakarta')->subDay()->format('Y-m-d H:i:s'),
            'titleID' => 'Infografis uji',
            'titleEN' => 'Test infographic',
            'imgID' => 'info-id.jpg',
            'imgEN' => 'info-en.jpg',
            'descriptionID' => 'Keterangan infografis.',
            'descriptionEN' => 'Infographic caption.',
            'slug' => 'infografis-uji',
            'status' => '1',
        ], $ubah));
    }

    // ── Rute dan lokal ────────────────────────────────────────────────────

    public function test_akar_dialihkan_ke_bahasa_inggris(): void
    {
        $this->get('/')->assertRedirect('/en');
    }

    public function test_landing_terbuka_di_kedua_bahasa(): void
    {
        foreach (['en', 'id'] as $lang) {
            $this->get("/$lang")->assertOk()->assertViewIs('frontends.landing');
        }
    }

    public function test_atribut_bahasa_dokumen_mengikuti_lokal(): void
    {
        $this->get('/id')->assertSee('<html lang="id"', false);
        $this->get('/en')->assertSee('<html lang="en"', false);
    }

    public function test_label_menu_diterjemahkan_mengikuti_lokal(): void
    {
        $this->get('/id')->assertSee('peta &amp; data', false)->assertSee('unduhan');
        $this->get('/en')->assertSee('map &amp; data', false)->assertSee('downloads');
    }

    // ── Navigasi ──────────────────────────────────────────────────────────

    /**
     * Menu landing harus menjangkau seluruh halaman publik. Daftar ini juga
     * berfungsi sebagai pagar: menghapus tujuan dari menu akan menggagalkan tes.
     */
    public function test_menu_menjangkau_seluruh_halaman_publik(): void
    {
        $respons = $this->get('/en');

        foreach ([
            '/en/about',
            '/en/faq',
            '/en/termsofuse',
            '/en/atbd?cat=monthly',
            '/en/atbd?cat=annual',
            '/en/refrencemap',
            '/en/newnevent',
            '/en/downloads',
            '/en/infographics',
            '/en/factsheet',
        ] as $tujuan) {
            $respons->assertSee($tujuan, false);
        }
    }

    public function test_seluruh_tujuan_menu_dapat_dibuka(): void
    {
        foreach ([
            '/en/about', '/en/faq', '/en/termsofuse', '/en/atbd?cat=monthly',
            '/en/atbd?cat=annual', '/en/refrencemap', '/en/newnevent',
            '/en/downloads', '/en/infographics', '/en/factsheet',
        ] as $tujuan) {
            $this->get($tujuan)->assertOk();
        }
    }

    public function test_pengalih_bahasa_menunjuk_kedua_lokal(): void
    {
        $this->get('/id')
            ->assertSee('hreflang="en"', false)
            ->assertSee('hreflang="id"', false)
            ->assertSee('aria-current="true"', false);
    }

    // ── Hero ──────────────────────────────────────────────────────────────

    /** Dulu menunjuk anchor #metodologi yang tidak pernah ada di halaman. */
    public function test_tombol_fact_sheet_menuju_halaman_fact_sheet(): void
    {
        $this->get('/en')
            ->assertSee('/en/factsheet', false)
            ->assertDontSee('#metodologi', false);
    }

    // ── Kabar ─────────────────────────────────────────────────────────────

    public function test_kabar_hanya_menampilkan_yang_sudah_terbit(): void
    {
        $this->terbitkanBerita(['titleEN' => 'Sudah terbit']);
        $this->terbitkanBerita([
            'titleEN' => 'Masih draf',
            'status' => '0',
            'slug' => 'draf',
        ]);
        $this->terbitkanBerita([
            'titleEN' => 'Terjadwal',
            'publishdate' => Carbon::now('Asia/Jakarta')->addWeek()->format('Y-m-d H:i:s'),
            'slug' => 'terjadwal',
        ]);

        $this->get('/en')
            ->assertSee('Sudah terbit')
            ->assertDontSee('Masih draf')
            ->assertDontSee('Terjadwal');
    }

    public function test_kabar_tidak_memuat_agenda(): void
    {
        $this->terbitkanBerita(['titleEN' => 'Berita biasa']);
        $this->terbitkanBerita([
            'titleEN' => 'Agenda lokakarya',
            'category' => 'event',
            'slug' => 'agenda',
        ]);

        $this->get('/en')
            ->assertSee('Berita biasa')
            ->assertDontSee('Agenda lokakarya');
    }

    public function test_kabar_dibatasi_dua_dan_terbaru_lebih_dulu(): void
    {
        foreach ([3, 1, 2, 4] as $hari) {
            $this->terbitkanBerita([
                'titleEN' => "Berita $hari hari lalu",
                'publishdate' => Carbon::now('Asia/Jakarta')->subDays($hari)->format('Y-m-d H:i:s'),
                'slug' => "berita-$hari",
            ]);
        }

        $isi = $this->get('/en')->assertOk()->getContent();

        $this->assertStringContainsString('Berita 1 hari lalu', $isi);
        $this->assertStringContainsString('Berita 2 hari lalu', $isi);
        $this->assertStringNotContainsString('Berita 3 hari lalu', $isi);
        $this->assertStringNotContainsString('Berita 4 hari lalu', $isi);
        $this->assertLessThan(
            strpos($isi, 'Berita 2 hari lalu'),
            strpos($isi, 'Berita 1 hari lalu'),
            'Kabar terbaru harus tampil lebih dulu.'
        );
    }

    /** Deskripsi berkolom teks biasa; markup yang terlanjur tersimpan disaring. */
    public function test_markup_pada_deskripsi_tidak_bocor_ke_halaman(): void
    {
        $this->terbitkanBerita([
            'descriptionEN' => '<p>Ringkasan dengan markup.</p>',
        ]);

        $this->get('/en')
            ->assertSee('Ringkasan dengan markup.')
            ->assertDontSee('&lt;p&gt;', false)
            ->assertDontSee('<p>Ringkasan dengan markup.</p>', false);
    }

    public function test_kabar_kosong_menampilkan_keterangan(): void
    {
        $this->get('/id')->assertSee('Belum ada kabar terbit.');
    }

    public function test_kabar_memakai_bahasa_yang_aktif(): void
    {
        $this->terbitkanBerita([
            'titleID' => 'Judul Indonesia',
            'titleEN' => 'English title',
        ]);

        $this->get('/id')->assertSee('Judul Indonesia')->assertDontSee('English title');
        $this->get('/en')->assertSee('English title')->assertDontSee('Judul Indonesia');
    }

    // ── Infografis ────────────────────────────────────────────────────────

    public function test_infografis_terbaru_yang_ditampilkan(): void
    {
        $this->terbitkanInfografis([
            'titleEN' => 'Infografis lama',
            'publishdate' => Carbon::now('Asia/Jakarta')->subMonth()->format('Y-m-d H:i:s'),
        ]);
        $this->terbitkanInfografis(['titleEN' => 'Infografis terbaru']);

        $this->get('/en')
            ->assertSee('Infografis terbaru')
            ->assertDontSee('Infografis lama');
    }

    public function test_infografis_kosong_menampilkan_keterangan(): void
    {
        $this->get('/id')->assertSee('Belum ada infografis terbit.');
    }

    public function test_petunjuk_perbesar_hanya_muncul_bila_ada_infografis(): void
    {
        $this->get('/id')->assertDontSee('Ketuk untuk memperbesar');

        $this->terbitkanInfografis();
        $this->get('/id')->assertSee('Ketuk untuk memperbesar');
    }

    // ── Footer ────────────────────────────────────────────────────────────

    public function test_footer_memuat_setiap_kelompok_menu(): void
    {
        $respons = $this->get('/en');

        foreach (['about', 'FAQ', 'map &amp; data', 'methodology', 'news &amp; event', 'downloads'] as $kelompok) {
            $respons->assertSee($kelompok, false);
        }
    }
}
