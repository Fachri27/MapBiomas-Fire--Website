<?php

namespace Tests\Feature;

use App\Livewire\FactsheetComponent;
use App\Livewire\FaqComponent;
use App\Livewire\FrontendInfographic;
use App\Livewire\FrontendNews;
use App\Livewire\ListInfographicComponent;
use App\Livewire\ListNewsComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Semua daftar memakai view paginasi yang sama (livewire.pagination), yang
 * memanggil gotoPage/nextPage/previousPage — metode dari trait WithPagination.
 * Tanpa trait itu tombol halaman melempar MethodNotFoundException dan search()
 * yang memanggil resetPage() ikut fatal. Tes ini menjaga keduanya.
 */
class PaginationTest extends TestCase
{
    use RefreshDatabase;

    /** Isi 15 baris supaya ada dua halaman pada paginate(10). */
    private function isi(string $tabel): void
    {
        for ($i = 1; $i <= 15; $i++) {
            // Nol di depan: tanpa itu "TITLE-1" ikut cocok dengan "TITLE-11".
            $n = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $baris = match ($tabel) {
                'factsheet' => [
                    'category' => 'monthly', 'titleID' => "JUDUL-{$n}", 'titleEN' => "TITLE-{$n}",
                    'descriptionID' => 'd', 'descriptionEN' => 'd', 'link' => '#',
                ],
                'faq' => [
                    'questionID' => "TANYA-{$n}", 'questionEN' => "ASK-{$n}",
                    'answerID' => '<p>a</p>', 'answerEN' => '<p>b</p>',
                ],
                'news' => [
                    'category' => 'news', 'publishdate' => now()->subDays($i)->format('Y-m-d H:i:s'),
                    'titleID' => "BERITA-{$n}", 'titleEN' => "NEWS-{$n}", 'img' => 'x.jpg',
                    'descriptionID' => 'd', 'descriptionEN' => 'd',
                    'contentID' => '<p>i</p>', 'contentEN' => '<p>c</p>',
                    'slug' => "berita-{$n}", 'status' => '1',
                ],
                'infographic' => [
                    'publishdate' => now()->subDays($i)->format('Y-m-d H:i:s'),
                    'titleID' => "INFO-{$n}", 'titleEN' => "GRAPHIC-{$n}", 'slug' => "info-{$n}",
                    'descriptionID' => 'd', 'descriptionEN' => 'd',
                    'imgID' => 'x.jpg', 'imgEN' => 'x.jpg', 'status' => 1,
                ],
            };

            DB::table($tabel)->insert($baris + [
                'created_at' => now()->subMinutes($i),
                'updated_at' => now(),
            ]);
        }
    }

    public static function daftarPaginasi(): array
    {
        return [
            'cms factsheet' => [FactsheetComponent::class, 'factsheet', 'TITLE-01', 'TITLE-15'],
            'cms faq' => [FaqComponent::class, 'faq', 'ASK-15', 'ASK-01'],
            'cms news' => [ListNewsComponent::class, 'news', 'BERITA-01', 'BERITA-15'],
            'cms infographic' => [ListInfographicComponent::class, 'infographic', 'GRAPHIC-01', 'GRAPHIC-15'],
            'frontend news' => [FrontendNews::class, 'news', 'NEWS-01', 'NEWS-15'],
            'frontend infographic' => [FrontendInfographic::class, 'infographic', 'GRAPHIC-01', 'GRAPHIC-15'],
        ];
    }

    /**
     * @test
     * @dataProvider daftarPaginasi
     */
    public function daftar_bisa_pindah_halaman(string $komponen, string $tabel, string $diHalaman1, string $diHalaman2): void
    {
        $this->isi($tabel);

        Livewire::test($komponen)
            ->assertSee($diHalaman1)
            ->assertDontSee($diHalaman2)
            ->call('nextPage')
            ->assertSee($diHalaman2)
            ->assertDontSee($diHalaman1)
            ->call('gotoPage', 1)
            ->assertSee($diHalaman1)
            ->call('previousPage')
            ->assertSee($diHalaman1);
    }

    /**
     * Infografis terbit bulanan. `period` boleh kosong pada entri lama, jadi
     * saringan harus jatuh ke bulan publishdate — dan harus mereset halaman.
     *
     * @test
     */
    public function saringan_bulan_infografis_menyaring_dan_mereset_halaman(): void
    {
        $this->isi('infographic');

        // Satu baris Juli 2026: period diisi walau terbitnya Agustus.
        DB::table('infographic')->insert([
            'publishdate' => '2026-08-05 00:00:00', 'period' => '2026-07',
            'titleID' => 'INFO-JULI', 'titleEN' => 'GRAPHIC-JULI', 'slug' => 'info-juli',
            'descriptionID' => 'd', 'descriptionEN' => 'd',
            'imgID' => 'x.jpg', 'imgEN' => 'x.jpg', 'status' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        // Satu baris tanpa period: bulannya diturunkan dari publishdate.
        DB::table('infographic')->insert([
            'publishdate' => '2026-06-10 00:00:00', 'period' => null,
            'titleID' => 'INFO-JUNI', 'titleEN' => 'GRAPHIC-JUNI', 'slug' => 'info-juni',
            'descriptionID' => 'd', 'descriptionEN' => 'd',
            'imgID' => 'x.jpg', 'imgEN' => 'x.jpg', 'status' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Livewire::test(FrontendInfographic::class)
            // Halaman 2 dulu: kalau resetPage() tak jalan, saringan tampil kosong.
            ->call('nextPage')
            ->set('period', '2026-07')
            ->assertSee('GRAPHIC-JULI')
            ->assertDontSee('GRAPHIC-JUNI')
            ->assertDontSee('GRAPHIC-01')
            ->set('period', '2026-06')
            ->assertSee('GRAPHIC-JUNI')
            ->assertDontSee('GRAPHIC-JULI');
    }

    /** @test */
    public function memfilter_mereset_ke_halaman_pertama(): void
    {
        $this->isi('faq');
        $this->isi('news');
        $this->isi('infographic');

        // Kotak pencarian memakai wire:model.live, jadi resetPage() harus jalan
        // lewat hook updatedQuery() — bukan lewat search() yang tak pernah dipanggil.
        Livewire::test(FaqComponent::class)
            ->call('nextPage')->assertDontSee('ASK-15')
            ->set('query', 'TANYA')->assertSee('ASK-15');

        Livewire::test(ListNewsComponent::class)
            ->call('nextPage')->assertDontSee('BERITA-01')
            ->set('query', 'BERITA')->assertSee('BERITA-01');

        Livewire::test(ListInfographicComponent::class)
            ->call('nextPage')->assertDontSee('GRAPHIC-01')
            ->set('query', 'GRAPHIC')->assertSee('GRAPHIC-01');

        // Select kategori berita publik juga wire:model.live.
        Livewire::test(FrontendNews::class)
            ->call('nextPage')->assertDontSee('NEWS-01')
            ->set('category', 'news')->assertSee('NEWS-01');
    }
}
