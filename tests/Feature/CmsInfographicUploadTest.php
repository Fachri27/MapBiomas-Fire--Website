<?php

namespace Tests\Feature;

use App\Livewire\AddInfographicComponent;
use App\Livewire\EditInfographicComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class CmsInfographicUploadTest extends TestCase
{
    use RefreshDatabase;

    private function terbitkanInfographic(array $overrides = []): int
    {
        return DB::table('infographic')->insertGetId(array_merge([
            'publishdate' => '2026-09-01',
            'period' => '2026-08',
            'category' => 'monthly',
            'titleID' => 'Infografis Uji',
            'titleEN' => 'Test Infographic Unique',
            'slug' => 'infografis-uji',
            'descriptionID' => 'Deskripsi Uji',
            'descriptionEN' => 'Test Description',
            'imgID' => 'old_id.jpg',
            'imgEN' => 'old_en.jpg',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_add_infographic_requires_valid_photo_on_submit(): void
    {
        Livewire::test(AddInfographicComponent::class)
            ->set('category', 'monthly')
            ->set('publishdate', '2026-09-01')
            ->set('titleID', 'Judul ID')
            ->set('titleEN', 'Title EN')
            ->set('descriptionID', 'Deskripsi ID')
            ->set('descriptionEN', 'Description EN')
            ->call('storePosts')
            ->assertNoRedirect();

        $this->assertSame(0, DB::table('infographic')->where('titleEN', 'Title EN')->count());
    }

    public function test_add_infographic_rejects_non_media_upload_in_hook_and_server(): void
    {
        Storage::fake('local');

        Livewire::test(AddInfographicComponent::class)
            ->set('photoID', UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'))
            ->assertSet('photoID', null);

        // When submitted with an invalid file, server-side manualValidation rejects it
        $instance = new AddInfographicComponent();
        $instance->category = 'monthly';
        $instance->publishdate = '2026-09-01';
        $instance->titleID = 'Judul ID';
        $instance->titleEN = 'Title EN';
        $instance->descriptionID = 'Deskripsi ID';
        $instance->descriptionEN = 'Description EN';
        $instance->photoID = UploadedFile::fake()->create('script.php', 10, 'text/x-php');
        $instance->photoEN = UploadedFile::fake()->image('valid.jpg');

        $this->assertFalse($instance->manualValidation());
    }

    public function test_add_infographic_rejects_file_exceeding_20mb(): void
    {
        Storage::fake('local');

        // Over 20MB (20481 KB) is rejected in hook
        Livewire::test(AddInfographicComponent::class)
            ->set('photoID', UploadedFile::fake()->create('too_big.png', 20481, 'image/png'))
            ->assertSet('photoID', null);

        // Exactly 20MB (20480 KB) is accepted in hook
        Livewire::test(AddInfographicComponent::class)
            ->set('photoID', UploadedFile::fake()->create('exact_20mb.png', 20480, 'image/png'))
            ->assertNotSet('photoID', null);
    }

    public function test_add_infographic_accepts_valid_media_and_stores(): void
    {
        Storage::fake('local');

        Livewire::test(AddInfographicComponent::class)
            ->set('category', 'monthly')
            ->set('publishdate', '2026-09-01')
            ->set('period', '2026-08')
            ->set('titleID', 'Judul Baru ID')
            ->set('titleEN', 'Unique Title EN')
            ->set('descriptionID', 'Deskripsi Baru ID')
            ->set('descriptionEN', 'Unique Description EN')
            ->set('photoID', UploadedFile::fake()->image('gambar-id.jpg'))
            ->set('photoEN', UploadedFile::fake()->image('gambar-en.png'))
            ->call('storePosts')
            ->assertRedirect('/cms/listinfographic');

        $record = DB::table('infographic')->where('titleEN', 'Unique Title EN')->first();
        $this->assertNotNull($record);
        $this->assertNotEmpty($record->imgID);
        $this->assertNotEmpty($record->imgEN);
        Storage::disk('local')->assertExists('public/files/photos/' . $record->imgID);
        Storage::disk('local')->assertExists('public/files/photos/' . $record->imgEN);
    }

    public function test_edit_infographic_locks_critical_properties(): void
    {
        $id = $this->terbitkanInfographic();

        $component = Livewire::test(EditInfographicComponent::class, ['id' => $id])
            ->assertSet('idInfographic', $id)
            ->assertSet('uphotoID', 'old_id.jpg')
            ->assertSet('uphotoEN', 'old_en.jpg');

        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('idInfographic', 9999);
    }

    public function test_edit_infographic_locks_uphoto_properties(): void
    {
        $id = $this->terbitkanInfographic();

        $component = Livewire::test(EditInfographicComponent::class, ['id' => $id]);

        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('uphotoID', 'hacked.jpg');
    }

    public function test_edit_infographic_validates_uploaded_files(): void
    {
        $id = $this->terbitkanInfographic();

        // Hook rejects invalid extension
        Livewire::test(EditInfographicComponent::class, ['id' => $id])
            ->set('photoID', UploadedFile::fake()->create('invalid.txt', 10, 'text/plain'))
            ->assertSet('photoID', null);

        // Server validation rejects invalid file on submission
        $instance = new EditInfographicComponent();
        $instance->idInfographic = $id;
        $instance->titleID = 'Judul Edit';
        $instance->titleEN = 'Title Edit';
        $instance->descriptionID = 'Deskripsi';
        $instance->descriptionEN = 'Description';
        $instance->publishdate = '2026-09-01';
        $instance->photoID = UploadedFile::fake()->create('invalid.exe', 10, 'application/x-msdownload');

        $this->assertFalse($instance->manualValidation());
    }

    public function test_edit_infographic_updates_photo_and_uses_basename_to_delete_old_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('public/files/photos/old_id.jpg', 'content');
        Storage::disk('local')->put('public/files/photos/thumbnail/old_id.jpg', 'thumb');

        $id = $this->terbitkanInfographic([
            'titleEN' => 'Edit Target EN',
            'imgID' => 'old_id.jpg',
            'imgEN' => 'old_en.jpg',
        ]);

        Livewire::test(EditInfographicComponent::class, ['id' => $id])
            ->set('titleID', 'Judul Baru')
            ->set('photoID', UploadedFile::fake()->image('new_id.png'))
            ->call('storePosts')
            ->assertRedirect('/cms/listinfographic');

        $updated = DB::table('infographic')->find($id);
        $this->assertSame('Judul Baru', $updated->titleID);
        $this->assertNotSame('old_id.jpg', $updated->imgID);
        $this->assertSame('old_en.jpg', $updated->imgEN);

        // Old file deleted via basename
        Storage::disk('local')->assertMissing('public/files/photos/old_id.jpg');
        Storage::disk('local')->assertMissing('public/files/photos/thumbnail/old_id.jpg');
        Storage::disk('local')->assertExists('public/files/photos/' . $updated->imgID);
    }

    public function test_edit_infographic_handle_photo_upload_sanitizes_path_with_basename(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('sensitive.txt', 'secret data');

        $component = new EditInfographicComponent();
        $component->photoID = UploadedFile::fake()->image('test.png');

        $reflection = new \ReflectionClass($component);
        $method = $reflection->getMethod('handlePhotoUpload');
        $method->invokeArgs($component, [
            $component->photoID,
            '../../sensitive.txt',
            'uploadImageID'
        ]);

        // sensitive.txt must not be deleted because basename strips '../../'
        Storage::disk('local')->assertExists('sensitive.txt');
    }
}
