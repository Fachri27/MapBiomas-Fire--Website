<?php

namespace Tests\Feature;

use App\Livewire\AddFactsheetComponent;
use App\Livewire\AddFaqComponrnt;
use App\Livewire\EditFactsheetComponent;
use App\Livewire\EditFaqComponent;
use App\Livewire\FactsheetComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CmsSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function terbitkanFactsheet(array $ubah = []): int
    {
        return DB::table('factsheet')->insertGetId(array_merge([
            'category' => 'monthly',
            'titleID' => 'JUDUL-BULANAN',
            'titleEN' => 'MONTHLY-TITLE',
            'descriptionID' => 'DESKRIPSI-BULANAN',
            'descriptionEN' => 'MONTHLY-DESCRIPTION',
            'linkID' => 'https://example.com/monthly.pdf',
            'linkEN' => 'https://example.com/monthly-en.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ], $ubah));
    }

    /** @test */
    public function cms_pages_require_session(): void
    {
        $this->get('/cms/listfactsheet')->assertRedirect('/cms/login');
        $this->get('/cms/listfaq')->assertRedirect('/cms/login');
    }

    /** @test */
    public function cms_factsheet_lists_all_entries_with_category(): void
    {
        $id = $this->terbitkanFactsheet();
        $this->terbitkanFactsheet(['category' => 'annual', 'titleID' => 'JUDUL-TAHUNAN', 'titleEN' => 'ANNUAL-TITLE']);

        $res = $this->withSession(['id' => 1])->get('/cms/listfactsheet');
        $res->assertOk()
            ->assertSee('MONTHLY-TITLE')
            ->assertSee('ANNUAL-TITLE')
            ->assertSee('monthly')
            ->assertSee('/cms/editfactsheet/'.$id);

        Livewire::test(FactsheetComponent::class)
            ->call('delete', $id)
            ->call('deleting', $id);

        $this->assertNull(DB::table('factsheet')->find($id));
    }

    /** @test */
    public function cms_factsheet_add_stores_new_entry(): void
    {
        Livewire::test(AddFactsheetComponent::class)
            ->set('category', 'annual')
            ->set('titleID', 'SAVED-ID')
            ->set('titleEN', 'SAVED-EN')
            ->set('descriptionID', 'desc-id')
            ->set('descriptionEN', 'desc-en')
            ->set('linkID', 'https://example.com/new-id.pdf')
            ->set('linkEN', 'https://example.com/new-en.pdf')
            ->call('storeAksi')
            ->assertRedirect('/cms/listfactsheet');

        $row = DB::table('factsheet')->where('titleEN', 'SAVED-EN')->first();
        $this->assertNotNull($row);
        $this->assertSame('annual', $row->category);
        $this->assertSame('https://example.com/new-id.pdf', $row->linkID);
        $this->assertSame('https://example.com/new-en.pdf', $row->linkEN);
    }

    /** @test */
    public function cms_factsheet_add_validates_required_fields(): void
    {
        Livewire::test(AddFactsheetComponent::class)
            ->set('titleID', 'x')->set('titleEN', 'y')
            ->set('descriptionID', 'a')->set('descriptionEN', 'b')
            ->set('linkID', 'https://example.com/x.pdf')
            ->set('linkEN', 'https://example.com/x.pdf')
            ->call('storeAksi');

        // Kategori kosong → tidak tersimpan.
        $this->assertSame(0, DB::table('factsheet')->count());
    }

    /** @test */
    public function cms_factsheet_add_stores_uploaded_pdf(): void
    {
        // store() menulis ke disk default (local), bukan disk 'public'.
        Storage::fake('local');

        Livewire::test(AddFactsheetComponent::class)
            ->set('category', 'monthly')
            ->set('titleID', 'PDF-ID')
            ->set('titleEN', 'PDF-EN')
            ->set('descriptionID', 'desc-id')
            ->set('descriptionEN', 'desc-en')
            // Link sengaja kosong: PDF saja sudah cukup.
            ->set('pdfID', UploadedFile::fake()->create('factsheet-id.pdf', 200, 'application/pdf'))
            ->set('pdfEN', UploadedFile::fake()->create('factsheet-en.pdf', 200, 'application/pdf'))
            ->call('storeAksi')
            ->assertRedirect('/cms/listfactsheet');

        $row = DB::table('factsheet')->where('titleEN', 'PDF-EN')->first();
        $this->assertNotNull($row);
        $this->assertNotEmpty($row->fileID);
        $this->assertNotEmpty($row->fileEN);
        // Dua unggahan berbeda: pembaca EN tidak ikut menerima PDF Indonesia.
        $this->assertNotSame($row->fileID, $row->fileEN);
        $this->assertSame('', $row->linkID);
        $this->assertSame('', $row->linkEN);
        Storage::disk('local')->assertExists('public/files/factsheet/'.$row->fileID);
        Storage::disk('local')->assertExists('public/files/factsheet/'.$row->fileEN);
    }

    /** @test */
    public function cms_factsheet_rejects_non_pdf_upload(): void
    {
        Storage::fake('local');

        Livewire::test(AddFactsheetComponent::class)
            ->set('pdfID', UploadedFile::fake()->image('bukan.jpg'))
            ->assertSet('pdfID', null);
    }

    /** @test */
    public function cms_factsheet_rejects_pdf_over_50mb(): void
    {
        Storage::fake('local');

        Livewire::test(AddFactsheetComponent::class)
            ->set('pdfEN', UploadedFile::fake()->create('gede.pdf', 51201, 'application/pdf'))
            ->assertSet('pdfEN', null);

        // Tepat di batas masih diterima.
        Livewire::test(AddFactsheetComponent::class)
            ->set('pdfEN', UploadedFile::fake()->create('pas.pdf', 51200, 'application/pdf'))
            ->assertNotSet('pdfEN', null);
    }

    /** @test */
    public function cms_factsheet_add_does_not_redirect_when_invalid(): void
    {
        Livewire::test(AddFactsheetComponent::class)
            ->set('category', 'monthly')
            ->call('storeAksi')
            ->assertNoRedirect();

        $this->assertSame(0, DB::table('factsheet')->count());
    }

    /** @test */
    public function cms_factsheet_edit_updates_entry(): void
    {
        $id = $this->terbitkanFactsheet();

        Livewire::test(EditFactsheetComponent::class, ['id' => $id])
            ->assertSet('titleEN', 'MONTHLY-TITLE')
            ->assertSet('category', 'monthly')
            ->set('linkEN', 'https://example.com/edited.pdf')
            ->call('storeAksi');

        $row = DB::table('factsheet')->find($id);
        $this->assertSame('https://example.com/edited.pdf', $row->linkEN);
        // Sisi Indonesia tidak ikut berubah.
        $this->assertSame('https://example.com/monthly.pdf', $row->linkID);
    }

    /**
     * Entri warisan memakai satu berkas untuk kedua bahasa. Mengganti PDF di
     * satu sisi tidak boleh menghapus berkas yang masih dipakai sisi lain.
     *
     * @test
     */
    public function cms_factsheet_edit_keeps_pdf_shared_with_other_language(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('public/files/factsheet/warisan.pdf', 'pdf');

        $id = $this->terbitkanFactsheet(['fileID' => 'warisan.pdf', 'fileEN' => 'warisan.pdf']);

        Livewire::test(EditFactsheetComponent::class, ['id' => $id])
            ->set('pdfID', UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf'))
            ->call('storeAksi');

        $row = DB::table('factsheet')->find($id);
        $this->assertNotSame('warisan.pdf', $row->fileID);
        $this->assertSame('warisan.pdf', $row->fileEN);
        Storage::disk('local')->assertExists('public/files/factsheet/warisan.pdf');
    }

    /** @test */
    public function cms_can_preview_unpublished_news(): void
    {
        $this->get('/cms/previewnews/1')->assertRedirect('/cms/login');

        $id = DB::table('news')->insertGetId([
            'category' => 'news',
            'publishdate' => now()->addDay()->format('Y-m-d H:i:s'),
            'titleID' => 'JUDUL-DRAFT',
            'titleEN' => 'DRAFT-TITLE',
            'img' => 'draf.jpg',
            'descriptionID' => '<p>ringkasan draf</p>',
            'descriptionEN' => '<p>draft summary</p>',
            'contentID' => '<p>isi draf</p>',
            'contentEN' => '<p>DRAFT-BODY</p>',
            'slug' => 'judul-draft',
            'status' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            // Draf tetap tersembunyi di halaman publik...
            $this->get('/en')->assertDontSee('DRAFT-TITLE');

            // ...tapi bisa dibuka lewat pratinjau CMS lengkap dengan bannernya.
            // Default EN, dan versi Indonesia bisa dimuat via ?lang=id.
            $this->withSession(['id' => 1])->get("/cms/previewnews/$id")
                ->assertOk()
                ->assertSee('DRAFT-TITLE')
                ->assertSee('DRAFT-BODY', false)
                ->assertSee('Pratinjau — berita ini belum dipublikasi');

            $this->withSession(['id' => 1])->get("/cms/previewnews/$id?lang=id")
                ->assertOk()
                ->assertSee('JUDUL-DRAFT')
                ->assertSee('isi draf', false);

            // Pratinjau kartu menampilkan thumbnail + judul + deskripsi dua bahasa.
            $this->withSession(['id' => 1])->get("/cms/previewcardnews/$id")
                ->assertOk()
                ->assertSee('storage/files/photos/draf.jpg', false)
                ->assertSee('JUDUL-DRAFT')
                ->assertSee('DRAFT-TITLE');
        } finally {
            DB::table('news')->where('id', $id)->delete();
        }
    }

    /** @test */
    public function cms_faq_add_edit_list_work_without_category(): void
    {
        $id = DB::table('faq')->insertGetId([
            'questionID' => 'TEST-PERTANYAAN',
            'questionEN' => 'TEST-QUESTION',
            'answerID' => '<p>a</p>',
            'answerEN' => '<p>b</p>',
            'created_at' => now(),
        ]);

        try {
            Livewire::test(AddFaqComponrnt::class)
                ->set('questionID', 'ADD-Q')
                ->set('questionEN', 'ADD-EN')
                ->set('answerID', 'ans-id')
                ->set('answerEN', 'ans-en')
                ->call('storeAksi')
                ->assertRedirect('/cms/listfaq');

            $added = DB::table('faq')->where('questionEN', 'ADD-EN')->first();
            $this->assertNotNull($added);
            $this->assertSame('ADD-Q', $added->questionID);
            DB::table('faq')->where('id', $added->id)->delete();

            Livewire::test(EditFaqComponent::class, ['id' => $id])
                ->assertSet('questionID', 'TEST-PERTANYAAN')
                ->set('questionEN', 'EDITED-EN')
                ->call('storeAksi');

            $this->assertSame('EDITED-EN', DB::table('faq')->find($id)->questionEN);

            $this->withSession(['id' => 1])->get('/cms/listfaq')
                ->assertOk()
                ->assertSee('EDITED-EN');
        } finally {
            DB::table('faq')->whereIn('id', [$id])->delete();
            DB::table('faq')->where('questionEN', 'ADD-EN')->delete();
        }
    }

    /** @test */
    public function frontend_factsheet_lists_entries_per_category(): void
    {
        // Entri dikenali lewat deskripsi dan tautannya: judul sengaja tidak
        // dirender di halaman publik, hanya dipakai sebagai penanda di CMS.
        $this->terbitkanFactsheet();
        $this->terbitkanFactsheet(['descriptionEN' => 'SECOND-MONTHLY-DESCRIPTION']);
        $this->terbitkanFactsheet([
            'category' => 'annual',
            'titleID' => 'JUDUL-TAHUNAN',
            'titleEN' => 'ANNUAL-TITLE',
            'descriptionID' => 'DESKRIPSI-TAHUNAN',
            'descriptionEN' => 'ANNUAL-DESCRIPTION',
            'linkID' => 'https://example.com/annual-id.pdf',
            'linkEN' => 'https://example.com/annual-en.pdf',
        ]);

        // Tanpa ?cat= halaman membuka tab tahunan — tab pertama sekaligus bawaan.
        $annual = $this->get('/en/factsheet');
        $annual->assertOk()
            ->assertSee('?cat=annual')
            ->assertSee('?cat=monthly')
            ->assertSee('ANNUAL-DESCRIPTION')
            ->assertSee('href="https://example.com/annual-en.pdf"', false)
            ->assertDontSee('ANNUAL-TITLE');
        $this->assertStringNotContainsString('MONTHLY-DESCRIPTION', $annual->getContent());

        $monthly = $this->get('/en/factsheet?cat=monthly');
        $monthly->assertOk()
            ->assertSee('MONTHLY-DESCRIPTION')
            ->assertSee('SECOND-MONTHLY-DESCRIPTION')
            ->assertSee('href="https://example.com/monthly-en.pdf"', false)
            ->assertDontSee('MONTHLY-TITLE');

        $this->get('/en/factsheet?cat=hack')->assertSee('ANNUAL-DESCRIPTION');

        // Unduhan ikut bahasa: edisi Indonesia menunjuk berkasnya sendiri.
        $this->get('/id/factsheet?cat=annual')->assertOk()
            ->assertSee('href="https://example.com/annual-id.pdf"', false)
            ->assertDontSee('https://example.com/annual-en.pdf', false);
    }

    /** @test */
    public function frontend_factsheet_shows_empty_state_when_no_data(): void
    {
        $this->get('/en/factsheet')
            ->assertOk()
            ->assertSee(__('Belum ada factsheet terbit.'));
    }

    /** @test */
    public function frontend_faq_lists_all_items_without_cat_filter(): void
    {
        $faqId = DB::table('faq')->insertGetId([
            'questionID' => 'FAQ-FRONT-Q',
            'questionEN' => 'FAQ-FRONT-QUESTION',
            'answerID' => '<p>x</p>',
            'answerEN' => '<p>y</p>',
            'created_at' => now(),
        ]);

        try {
            foreach (['/en/faq', '/en/faq?cat=monthly', '/en/faq?cat=annual'] as $url) {
                $res = $this->get($url);
                $res->assertOk()->assertSee('FAQ-FRONT-QUESTION');
            }
        } finally {
            DB::table('faq')->where('id', $faqId)->delete();
        }
    }
}
