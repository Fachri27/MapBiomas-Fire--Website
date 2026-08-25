<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Masmerise\Toaster\Toaster;

class AddFactsheetComponent extends Component
{
    use WithFileUploads;

    public $category, $titleEN, $titleID, $descriptionEN, $descriptionID;
    // Unduhan ikut bahasa: tiap edisi punya PDF/tautannya sendiri.
    public $linkID, $linkEN, $pdfID, $pdfEN;

    public function updated($name, $value){
        if (in_array($name, ['pdfID', 'pdfEN']) && ! $this->pdfValid($value)) {
            $this->reset($name);
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
                'linkID' => $this->linkID ?? '',
                'linkEN' => $this->linkEN ?? '',
                'fileID' => $this->uploadPdf($this->pdfID),
                'fileEN' => $this->uploadPdf($this->pdfEN),
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

    public function uploadPdf($pdf){
        if (! $pdf) {
            return null;
        }
        // Disk eksplisit: disk sementara Livewire berbeda saat pengujian.
        $pdf->store('public/files/factsheet', 'local');
        return $pdf->hashName();
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
        }elseif($this->linkID == '' && ! $this->pdfID ){
            Toaster::error('Upload a PDF or fill the link for indonesia!');
            return;
        }elseif($this->linkEN == '' && ! $this->pdfEN ){
            Toaster::error('Upload a PDF or fill the link for english!');
            return;
        }elseif($this->pdfID && ! $this->pdfValid($this->pdfID) ){
            return;
        }elseif($this->pdfEN && ! $this->pdfValid($this->pdfEN) ){
            return;
        }
        return true;
    }
}
