<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Tiap unggahan di CMS harus punya bilah kemajuan. Pemeriksaan ini menjaga
 * unggahan baru tidak lolos tanpa bilahnya — gagalnya di sini, bukan di
 * keluhan editor yang menatap "Uploading..." tanpa tahu sisa berapa.
 */
class UploadProgressTest extends TestCase
{
    public function test_setiap_input_berkas_punya_bilah_kemajuan(): void
    {
        $berkas = glob(resource_path('views/livewire/*.blade.php'));
        $this->assertNotEmpty($berkas);

        $totalInput = 0;
        foreach ($berkas as $path) {
            $isi = file_get_contents($path);
            $input = preg_match_all('/type=[\'"]file[\'"]/', $isi);
            $totalInput += $input;

            $this->assertSame(
                $input,
                substr_count($isi, "@include('partials.uploadProgress')"),
                basename($path).': jumlah bilah kemajuan tidak sama dengan jumlah input berkas.'
            );

            // Bilahnya butuh state dari induk ber-x-data.
            if ($input > 0) {
                $this->assertSame($input, substr_count($isi, 'isUploading: false, progress: 0'), basename($path));
            }
        }

        // Batas bawah, bukan angka pasti: unggahan baru yang benar tidak
        // boleh menggagalkan uji ini — yang dijaga adalah rasio di atas.
        $this->assertGreaterThanOrEqual(10, $totalInput);
    }
}
