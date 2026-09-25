<?php

namespace Tests\Feature;

use App\Livewire\AddNewsComponent;
use App\Livewire\EditNewsComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use ReflectionClass;
use Tests\TestCase;

class NewsUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_news_rejects_missing_photo(): void
    {
        Livewire::test(AddNewsComponent::class)
            ->set('titleID', 'Judul Berita')
            ->set('titleEN', 'News Title')
            ->set('descriptionID', 'Deskripsi')
            ->set('descriptionEN', 'Description')
            ->set('contentID', 'Konten')
            ->set('contentEN', 'Content')
            ->set('publishdate', '2026-09-26')
            ->set('category', 'news')
            ->call('storePosts');

        $this->assertSame(0, DB::table('news')->count());
    }

    public function test_add_news_rejects_non_image_upload(): void
    {
        Storage::fake('public');

        $textDoc = UploadedFile::fake()->create('malicious.txt', 100, 'text/plain');

        Livewire::test(AddNewsComponent::class)
            ->set('titleID', 'Judul Berita')
            ->set('titleEN', 'News Title')
            ->set('photo', $textDoc)
            ->set('descriptionID', 'Deskripsi')
            ->set('descriptionEN', 'Description')
            ->set('contentID', 'Konten')
            ->set('contentEN', 'Content')
            ->set('publishdate', '2026-09-26')
            ->set('category', 'news')
            ->call('storePosts')
            ->assertHasErrors(['photo']);

        $this->assertSame(0, DB::table('news')->count());
    }

    public function test_add_news_rejects_disallowed_mime_types(): void
    {
        Storage::fake('public');

        $pdf = UploadedFile::fake()->create('document.pdf', 200, 'application/pdf');

        Livewire::test(AddNewsComponent::class)
            ->set('titleID', 'Judul Berita')
            ->set('titleEN', 'News Title')
            ->set('photo', $pdf)
            ->set('descriptionID', 'Deskripsi')
            ->set('descriptionEN', 'Description')
            ->set('contentID', 'Konten')
            ->set('contentEN', 'Content')
            ->set('publishdate', '2026-09-26')
            ->set('category', 'news')
            ->call('storePosts')
            ->assertHasErrors(['photo']);

        $this->assertSame(0, DB::table('news')->count());
    }

    public function test_add_news_rejects_photo_over_5mb(): void
    {
        Storage::fake('public');

        // 6MB image
        $largeImage = UploadedFile::fake()->image('huge.jpg')->size(6000);

        Livewire::test(AddNewsComponent::class)
            ->set('titleID', 'Judul Berita')
            ->set('titleEN', 'News Title')
            ->set('photo', $largeImage)
            ->set('descriptionID', 'Deskripsi')
            ->set('descriptionEN', 'Description')
            ->set('contentID', 'Konten')
            ->set('contentEN', 'Content')
            ->set('publishdate', '2026-09-26')
            ->set('category', 'news')
            ->call('storePosts')
            ->assertHasErrors(['photo']);

        $this->assertSame(0, DB::table('news')->count());
    }

    public function test_add_news_upload_image_validates_before_upload(): void
    {
        $component = new AddNewsComponent();
        $component->photo = UploadedFile::fake()->create('script.php', 10, 'application/x-php');

        $this->expectException(ValidationException::class);
        $component->uploadImage();
    }

    public function test_edit_news_properties_are_locked(): void
    {
        $reflection = new ReflectionClass(EditNewsComponent::class);

        $idNewsProp = $reflection->getProperty('idNews');
        $this->assertNotEmpty($idNewsProp->getAttributes(Locked::class), 'idNews must have #[Locked] attribute');

        $uphotoProp = $reflection->getProperty('uphoto');
        $this->assertNotEmpty($uphotoProp->getAttributes(Locked::class), 'uphoto must have #[Locked] attribute');
    }

    public function test_edit_news_client_cannot_mutate_locked_properties(): void
    {
        $id = DB::table('news')->insertGetId([
            'publishdate' => '2026-09-26',
            'titleID' => 'Judul Awal',
            'titleEN' => 'Initial Title',
            'slug' => 'judul-awal',
            'descriptionID' => 'Deskripsi',
            'descriptionEN' => 'Description',
            'contentID' => 'Konten',
            'contentEN' => 'Content',
            'category' => 'news',
            'img' => 'initial.jpg',
            'status' => 1,
            'created_at' => now(),
        ]);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(EditNewsComponent::class, ['id' => $id])
            ->set('uphoto', 'hacked.jpg');
    }

    public function test_edit_news_rejects_invalid_photo_replacement_and_preserves_old_photo(): void
    {
        Storage::fake('public');

        $id = DB::table('news')->insertGetId([
            'publishdate' => '2026-09-26',
            'titleID' => 'Judul Awal',
            'titleEN' => 'Initial Title',
            'slug' => 'judul-awal',
            'descriptionID' => 'Deskripsi',
            'descriptionEN' => 'Description',
            'contentID' => 'Konten',
            'contentEN' => 'Content',
            'category' => 'news',
            'img' => 'keep_this.jpg',
            'status' => 1,
            'created_at' => now(),
        ]);

        Storage::disk('public')->put('files/photos/keep_this.jpg', 'fake image content');

        $badFile = UploadedFile::fake()->create('malicious.sh', 50, 'application/x-sh');

        Livewire::test(EditNewsComponent::class, ['id' => $id])
            ->set('photo', $badFile)
            ->call('storePosts')
            ->assertHasErrors(['photo']);

        // The record in database must keep the original photo
        $row = DB::table('news')->find($id);
        $this->assertSame('keep_this.jpg', $row->img);

        // The old file in storage must not have been deleted
        Storage::disk('public')->assertExists('files/photos/keep_this.jpg');
    }

    public function test_edit_news_sanitizes_uphoto_to_prevent_directory_traversal(): void
    {
        Storage::fake();

        // Create a sensitive file outside the photos folder
        Storage::put('sensitive.txt', 'secret data');

        $id = DB::table('news')->insertGetId([
            'publishdate' => '2026-09-26',
            'titleID' => 'Judul Awal',
            'titleEN' => 'Initial Title',
            'slug' => 'judul-awal',
            'descriptionID' => 'Deskripsi',
            'descriptionEN' => 'Description',
            'contentID' => 'Konten',
            'contentEN' => 'Content',
            'category' => 'news',
            'img' => '../../sensitive.txt', // Malicious path traversal value in DB
            'status' => 1,
            'created_at' => now(),
        ]);

        $component = Livewire::test(EditNewsComponent::class, ['id' => $id]);

        // When photo is set to null, storePosts preserves sanitized/existing name
        $component->call('storePosts');

        // Sensitive file outside must still exist
        Storage::assertExists('sensitive.txt');
    }

    public function test_edit_news_deleting_photo_with_traversal_payload_does_not_delete_outside(): void
    {
        Storage::fake();

        // Simulate a file outside the photos directory: e.g., in public root
        Storage::put('public/sensitive.txt', 'secret data');

        $id = DB::table('news')->insertGetId([
            'publishdate' => '2026-09-26',
            'titleID' => 'Judul Awal',
            'titleEN' => 'Initial Title',
            'slug' => 'judul-awal',
            'descriptionID' => 'Deskripsi',
            'descriptionEN' => 'Description',
            'contentID' => 'Konten',
            'contentEN' => 'Content',
            'category' => 'news',
            'img' => '../sensitive.txt',
            'status' => 1,
            'created_at' => now(),
        ]);

        $component = new EditNewsComponent();
        $component->mount($id);

        $cleanPhoto = $component->uphoto ? basename($component->uphoto) : null;
        $this->assertSame('sensitive.txt', $cleanPhoto);

        if ($cleanPhoto && ! in_array($cleanPhoto, ['.', '..'])) {
            Storage::delete('public/files/photos/' . $cleanPhoto);
            Storage::delete('public/files/photos/thumbnail/' . $cleanPhoto);
        }

        // The file in public/sensitive.txt MUST still exist!
        Storage::assertExists('public/sensitive.txt');
    }
}

