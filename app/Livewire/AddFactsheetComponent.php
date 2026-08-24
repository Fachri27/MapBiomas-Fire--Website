<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Masmerise\Toaster\Toaster;

class AddFactsheetComponent extends Component
{
    use WithFileUploads;

    public $category, $titleEN, $titleID, $descriptionEN, $descriptionID, $link, $pdf;

    public function updatedPdf($file){
        if (! $this->pdfValid($file)) {
            $this->reset('pdf');
        }
    }

    public function storeAksi(){
        if($this->manualValidation()){
            DB::table('factsheet')->insert([
                'category' => $this->category,
                'titleID' => $this->titleID,
                'titleEN' => $this->titleEN,
                'descriptionID' => $this->descriptionID,
                'descriptionEN' => $this->descriptionEN,
                // Kolom NOT NULL: kalau hanya PDF yang diunggah, simpan string kosong.
                'link' => $this->link ?? '',
                'file' => $this->uploadPdf(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            redirect()->to('/cms/listfactsheet');
        }
    }

    public function render()
    {
        return view('livewire.add-factsheet-component');
    }

    public function uploadPdf(){
        if (! $this->pdf) {
            return null;
        }
        // Disk eksplisit: disk sementara Livewire berbeda saat pengujian.
        $this->pdf->store('public/files/factsheet', 'local');
        return $this->pdf->hashName();
    }

    public function pdfValid($file){
        if (strtolower($file->getClientOriginalExtension()) !== 'pdf') {
            Toaster::error('File must be a PDF!');
            return false;
        }
        // Batas 50MB disetel juga di config/livewire.php; tolak lebih awal dengan pesan jelas.
        if ($file->getSize() > 50 * 1024 * 1024) {
            Toaster::error('PDF is too large (max 50MB)!');
            return false;
        }
        return true;
    }

    public function manualValidation(){
        if(! in_array($this->category, ['monthly', 'annual'])){
            Toaster::error('Category is required!');
            return;
        }elseif($this->titleID == ''){
            Toaster::error('Title indonesia is required!');
            return;
        }elseif($this->descriptionID == '' ){
            Toaster::error('Description indonesia is required!');
            return;
        }elseif($this->titleEN == '' ){
            Toaster::error('Title english is required!');
            return;
        }elseif($this->descriptionEN == '' ){
            Toaster::error('Description english is required!');
            return;
        }elseif($this->link == '' && ! $this->pdf ){
            Toaster::error('Upload a PDF or fill the link!');
            return;
        }elseif($this->pdf && ! $this->pdfValid($this->pdf) ){
            return;
        }
        return true;
    }
}
