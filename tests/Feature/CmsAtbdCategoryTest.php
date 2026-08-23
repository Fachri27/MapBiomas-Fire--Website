<?php

namespace Tests\Feature;

use App\Livewire\PageAtbd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pergantian kategori pada layar CMS ATBD.
 *
 * Dulu setiap pergantian memicu redirect — satu muat ulang halaman penuh plus
 * pembangunan ulang dua editor TinyMCE. Sekarang cukup satu permintaan kecil,
 * jadi tesnya menjaga agar redirect itu tidak diam-diam kembali.
 */
class CmsAtbdCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function isiAtbd(string $category, string $en, string $id): void
    {
        DB::table('pageatbd')->insert([
            'name' => 'atbd-'.$category,
            'category' => $category,
            'contentEN' => $en,
            'contentID' => $id,
        ]);
    }

    public function test_ganti_kategori_tidak_memuat_ulang_halaman(): void
    {
        $this->isiAtbd('monthly', 'EN bulanan', 'ID bulanan');
        $this->isiAtbd('annual', 'EN tahunan', 'ID tahunan');

        Livewire::test(PageAtbd::class)
            ->set('category', 'annual')
            ->assertNoRedirect();
    }

    public function test_ganti_kategori_menukar_konten_kedua_bahasa(): void
    {
        $this->isiAtbd('monthly', 'EN bulanan', 'ID bulanan');
        $this->isiAtbd('annual', 'EN tahunan', 'ID tahunan');

        Livewire::test(PageAtbd::class)
            ->assertSet('contentEN', 'EN bulanan')
            ->assertSet('contentID', 'ID bulanan')
            ->set('category', 'annual')
            ->assertSet('contentEN', 'EN tahunan')
            ->assertSet('contentID', 'ID tahunan');
    }

    /** Editor dibungkus wire:ignore, jadi isinya harus didorong lewat peristiwa. */
    public function test_konten_baru_dikirim_ke_peramban_untuk_editor(): void
    {
        $this->isiAtbd('monthly', 'EN bulanan', 'ID bulanan');
        $this->isiAtbd('annual', 'EN tahunan', 'ID tahunan');

        Livewire::test(PageAtbd::class)
            ->set('category', 'annual')
            ->assertDispatched('atbd-konten-diganti', contentEN: 'EN tahunan', contentID: 'ID tahunan');
    }

    public function test_kategori_tanpa_isi_mengosongkan_editor(): void
    {
        $this->isiAtbd('monthly', 'EN bulanan', 'ID bulanan');

        Livewire::test(PageAtbd::class)
            ->set('category', 'annual')
            ->assertSet('contentEN', '')
            ->assertSet('contentID', '');
    }

    public function test_kategori_ngawur_jatuh_ke_bulanan(): void
    {
        $this->isiAtbd('monthly', 'EN bulanan', 'ID bulanan');

        Livewire::withQueryParams(['cat' => 'sembarang'])
            ->test(PageAtbd::class)
            ->assertSet('category', 'monthly')
            ->assertSet('contentEN', 'EN bulanan');

        Livewire::test(PageAtbd::class)
            ->set('category', 'sembarang')
            ->assertSet('category', 'monthly');
    }

    /** Membuka ?cat=annual langsung harus memuat edisi tahunan. */
    public function test_kategori_dibaca_dari_query_saat_halaman_dibuka(): void
    {
        $this->isiAtbd('monthly', 'EN bulanan', 'ID bulanan');
        $this->isiAtbd('annual', 'EN tahunan', 'ID tahunan');

        Livewire::withQueryParams(['cat' => 'annual'])
            ->test(PageAtbd::class)
            ->assertSet('category', 'annual')
            ->assertSet('contentEN', 'EN tahunan');
    }

    /**
     * Tanpa pendengar ini, konten baru sampai ke server tapi tidak pernah masuk
     * ke editor — pengguna melihat teks lama walau kategorinya sudah berganti.
     */
    public function test_halaman_cms_memuat_pendengar_penukar_konten(): void
    {
        $this->isiAtbd('monthly', 'EN bulanan', 'ID bulanan');

        $this->withSession(['id' => 1])
            ->get('/cms/cmsatbd')
            ->assertOk()
            ->assertSee("Livewire.on('atbd-konten-diganti'", false)
            ->assertSee('tinymce.get(nama)', false);
    }

    /**
     * Di Livewire 3, wire:model bersifat ditunda — nilainya baru terkirim pada
     * permintaan berikutnya. Tanpa .live, memilih kategori tidak menghasilkan
     * apa-apa sampai ada interaksi lain, sehingga editor masih menampilkan isi
     * kategori sebelumnya. Tes komponen tidak bisa menangkap ini karena ->set()
     * memanggil properti langsung dan melewati pengikatan di markup.
     */
    public function test_pilihan_kategori_terkirim_seketika(): void
    {
        $this->isiAtbd('monthly', 'EN bulanan', 'ID bulanan');

        $this->withSession(['id' => 1])
            ->get('/cms/cmsatbd')
            ->assertOk()
            ->assertSee("wire:model.live='category'", false);
    }

    public function test_simpan_menulis_ke_kategori_yang_sedang_dibuka(): void
    {
        $this->isiAtbd('monthly', 'EN bulanan', 'ID bulanan');
        $this->isiAtbd('annual', 'EN tahunan', 'ID tahunan');

        Livewire::test(PageAtbd::class)
            ->set('category', 'annual')
            ->set('contentEN', 'EN tahunan baru')
            ->set('contentID', 'ID tahunan baru')
            ->call('storePage');

        $this->assertSame(
            'EN tahunan baru',
            DB::table('pageatbd')->where('category', 'annual')->value('contentEN')
        );
        $this->assertSame(
            'EN bulanan',
            DB::table('pageatbd')->where('category', 'monthly')->value('contentEN'),
            'Edisi bulanan tidak boleh ikut tertimpa.'
        );
    }
}
