<?php

namespace Tests\Feature;

use App\Livewire\AddFactsheetComponent;
use App\Livewire\AddFaqComponrnt;
use App\Livewire\EditFactsheetComponent;
use App\Livewire\EditFaqComponent;
use App\Livewire\FactsheetComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'link' => 'https://example.com/monthly.pdf',
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
            ->call('storeDelete');

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
            ->set('link', 'https://example.com/new.pdf')
            ->call('storeAksi')
            ->assertRedirect('/cms/listfactsheet');

        $row = DB::table('factsheet')->where('titleEN', 'SAVED-EN')->first();
        $this->assertNotNull($row);
        $this->assertSame('annual', $row->category);
        $this->assertSame('https://example.com/new.pdf', $row->link);
    }

    /** @test */
    public function cms_factsheet_add_validates_required_fields(): void
    {
        Livewire::test(AddFactsheetComponent::class)
            ->set('titleID', 'x')->set('titleEN', 'y')
            ->set('descriptionID', 'a')->set('descriptionEN', 'b')
            ->set('link', 'https://example.com/x.pdf')
            ->call('storeAksi');

        // Kategori kosong → tidak tersimpan.
        $this->assertSame(0, DB::table('factsheet')->count());
    }

    /** @test */
    public function cms_factsheet_edit_updates_entry(): void
    {
        $id = $this->terbitkanFactsheet();

        Livewire::test(EditFactsheetComponent::class, ['id' => $id])
            ->assertSet('titleEN', 'MONTHLY-TITLE')
            ->assertSet('category', 'monthly')
            ->set('link', 'https://example.com/edited.pdf')
            ->call('storeAksi');

        $this->assertSame('https://example.com/edited.pdf', DB::table('factsheet')->find($id)->link);
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
            'link' => 'https://example.com/annual.pdf',
        ]);

        $monthly = $this->get('/en/factsheet');
        $monthly->assertOk()
            ->assertSee('?cat=monthly')
            ->assertSee('?cat=annual')
            ->assertSee('MONTHLY-DESCRIPTION')
            ->assertSee('SECOND-MONTHLY-DESCRIPTION')
            ->assertSee('href="https://example.com/monthly.pdf"', false)
            ->assertDontSee('MONTHLY-TITLE');

        $annual = $this->get('/en/factsheet?cat=annual');
        $annual->assertOk()
            ->assertSee('ANNUAL-DESCRIPTION')
            ->assertSee('href="https://example.com/annual.pdf"', false)
            ->assertDontSee('ANNUAL-TITLE');
        $this->assertStringNotContainsString('MONTHLY-DESCRIPTION', $annual->getContent());

        $this->get('/en/factsheet?cat=hack')->assertSee('MONTHLY-DESCRIPTION');
    }

    /** @test */
    public function frontend_factsheet_shows_empty_state_when_no_data(): void
    {
        $this->get('/en/factsheet')
            ->assertOk()
            ->assertSee(__('Belum ada fact sheet terbit.'));
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
