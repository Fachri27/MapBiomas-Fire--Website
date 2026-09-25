<?php

namespace Tests\Feature;

use App\Livewire\AddFactsheetComponent;
use App\Livewire\EditFactsheetComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class FactsheetUploadHardeningTest extends TestCase
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
            'fileID' => 'old_id.pdf',
            'fileEN' => 'old_en.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ], $ubah));
    }

    private function makeFakePdf(string $name = 'test.pdf', int $kb = 100, string $content = "%PDF-1.4\nSample content", ?string $mime = 'application/pdf'): UploadedFile
    {
        $file = UploadedFile::fake()->create($name, $kb, $mime);
        file_put_contents($file->getRealPath(), $content);
        return $file;
    }

    public function test_add_factsheet_pdf_valid_checks_extension_mime_magic_bytes_and_size(): void
    {
        $component = new AddFactsheetComponent();

        // 1. Invalid extension (.txt)
        $invalidExt = UploadedFile::fake()->create('test.txt', 10, 'application/pdf');
        file_put_contents($invalidExt->getRealPath(), "%PDF-1.4\nValid magic");
        $this->assertFalse($component->pdfValid($invalidExt));

        // 2. Invalid MIME type (image/png)
        $invalidMime = UploadedFile::fake()->create('test.pdf', 10, 'image/png');
        file_put_contents($invalidMime->getRealPath(), "%PDF-1.4\nValid magic");
        $this->assertFalse($component->pdfValid($invalidMime));

        // 3. Invalid magic bytes (disguised php script)
        $invalidMagic = UploadedFile::fake()->create('evil.pdf', 10, 'application/pdf');
        file_put_contents($invalidMagic->getRealPath(), "<?php echo 'hack'; ?>");
        $this->assertFalse($component->pdfValid($invalidMagic));

        // 4. File size over 50MB (50MB = 51200 KB)
        $tooLarge = UploadedFile::fake()->create('large.pdf', 51201, 'application/pdf');
        file_put_contents($tooLarge->getRealPath(), "%PDF-1.4\nValid magic");
        $this->assertFalse($component->pdfValid($tooLarge));

        // 5. Valid PDF (exactly 50MB)
        $exactLimit = UploadedFile::fake()->create('exact.pdf', 51200, 'application/pdf');
        file_put_contents($exactLimit->getRealPath(), "%PDF-1.4\nValid magic");
        $this->assertTrue($component->pdfValid($exactLimit));

        // 6. Valid PDF (normal size, uppercase extension)
        $validPdf = UploadedFile::fake()->create('doc.PDF', 500, 'application/pdf');
        file_put_contents($validPdf->getRealPath(), "%PDF-1.7\nBinary payload");
        $this->assertTrue($component->pdfValid($validPdf));
    }

    public function test_edit_factsheet_pdf_valid_checks_extension_mime_magic_bytes_and_size(): void
    {
        $component = new EditFactsheetComponent();

        // 1. Invalid extension
        $invalidExt = UploadedFile::fake()->create('test.exe', 10, 'application/pdf');
        file_put_contents($invalidExt->getRealPath(), "%PDF-1.4\nValid magic");
        $this->assertFalse($component->pdfValid($invalidExt));

        // 2. Invalid MIME type
        $invalidMime = UploadedFile::fake()->create('test.pdf', 10, 'text/plain');
        file_put_contents($invalidMime->getRealPath(), "%PDF-1.4\nValid magic");
        $this->assertFalse($component->pdfValid($invalidMime));

        // 3. Invalid magic bytes
        $invalidMagic = UploadedFile::fake()->create('notpdf.pdf', 10, 'application/pdf');
        file_put_contents($invalidMagic->getRealPath(), "NOT_A_PDF_CONTENT");
        $this->assertFalse($component->pdfValid($invalidMagic));

        // 4. File size over 50MB
        $tooLarge = UploadedFile::fake()->create('large.pdf', 51201, 'application/pdf');
        file_put_contents($tooLarge->getRealPath(), "%PDF-1.4\nValid magic");
        $this->assertFalse($component->pdfValid($tooLarge));

        // 5. Valid PDF
        $validPdf = $this->makeFakePdf('valid.pdf', 250);
        $this->assertTrue($component->pdfValid($validPdf));
    }

    public function test_edit_factsheet_properties_have_locked_attribute(): void
    {
        $ref = new ReflectionClass(EditFactsheetComponent::class);

        foreach (['idFactsheet', 'updfID', 'updfEN'] as $propName) {
            $this->assertTrue($ref->hasProperty($propName), "Property {$propName} should exist.");
            $prop = $ref->getProperty($propName);
            $this->assertTrue($prop->isPublic(), "Property {$propName} must be public.");
            $lockedAttrs = $prop->getAttributes(Locked::class);
            $this->assertNotEmpty($lockedAttrs, "Property \${$propName} must have #[Locked] attribute.");
        }
    }

    public function test_edit_factsheet_locked_properties_cannot_be_mutated_by_client(): void
    {
        $id = $this->terbitkanFactsheet();

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(EditFactsheetComponent::class, ['id' => $id])
            ->set('idFactsheet', 9999);
    }

    public function test_edit_factsheet_locked_updf_cannot_be_mutated_by_client(): void
    {
        $id = $this->terbitkanFactsheet();

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(EditFactsheetComponent::class, ['id' => $id])
            ->set('updfID', 'malicious.pdf');
    }

    public function test_edit_factsheet_locked_updf_en_cannot_be_mutated_by_client(): void
    {
        $id = $this->terbitkanFactsheet();

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(EditFactsheetComponent::class, ['id' => $id])
            ->set('updfEN', 'malicious.pdf');
    }

    public function test_handle_pdf_upload_applies_basename_preventing_path_traversal_deletion(): void
    {
        Storage::fake('local');

        // Create a sensitive file outside the factsheet folder on the local disk
        Storage::disk('local')->put('sensitive.txt', 'secret data');
        Storage::disk('local')->put('public/files/factsheet/legit.pdf', 'legit factsheet');

        $component = new EditFactsheetComponent();
        $refMethod = new ReflectionMethod(EditFactsheetComponent::class, 'handlePdfUpload');
        $refMethod->setAccessible(true);

        $uploadedPdf = $this->makeFakePdf('new.pdf', 100);

        // Attempt path traversal via $lama
        $traversalLama = '../../sensitive.txt';
        $newFilename = $refMethod->invoke($component, $uploadedPdf, $traversalLama, 'other.pdf');

        // The sensitive file outside the factsheet directory MUST NOT be deleted
        Storage::disk('local')->assertExists('sensitive.txt');
        $this->assertSame('secret data', Storage::disk('local')->get('sensitive.txt'));

        // The new file should have been uploaded
        Storage::disk('local')->assertExists('public/files/factsheet/'.$newFilename);
    }

    public function test_handle_pdf_upload_deletes_legitimate_old_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('public/files/factsheet/old_file.pdf', 'old pdf content');

        $component = new EditFactsheetComponent();
        $refMethod = new ReflectionMethod(EditFactsheetComponent::class, 'handlePdfUpload');
        $refMethod->setAccessible(true);

        $uploadedPdf = $this->makeFakePdf('new_file.pdf', 100);

        $newFilename = $refMethod->invoke($component, $uploadedPdf, 'old_file.pdf', 'other.pdf');

        // Old file deleted
        Storage::disk('local')->assertMissing('public/files/factsheet/old_file.pdf');
        // New file exists
        Storage::disk('local')->assertExists('public/files/factsheet/'.$newFilename);
    }
}
