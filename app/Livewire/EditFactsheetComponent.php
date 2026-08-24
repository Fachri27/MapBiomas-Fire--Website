<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Masmerise\Toaster\Toaster;

class EditFactsheetComponent extends Component
{
    use WithFileUploads;

    public $category, $titleEN, $titleID, $descriptionEN, $descriptionID, $link, $idFactsheet;
    public $pdf, $updf;

    public function mount($id){
        $data = DB::table('factsheet')->where('id', $id)->first();
        $this->category = $data->category;
        $this->titleID = $data->titleID;
        $this->titleEN = $data->titleEN;
        $this->descriptionID = $data->descriptionID;
        $this->descriptionEN = $data->descriptionEN;
        $this->link = $data->link;
        $this->updf = $data->file;
        $this->idFactsheet = $id;
    }

    public function updatedPdf($file){
        if (! $this->pdfValid($file)) {
            $this->reset('pdf');
        }
    }

    public function storeAksi(){
        if($this->manualValidation()){
            DB::table('factsheet')->where('id', $this->idFactsheet)->update([
                'category' => $this->category,
                'titleID' => $this->titleID,
                'titleEN' => $this->titleEN,
                'descriptionID' => $this->descriptionID,
                'descriptionEN' => $this->descriptionEN,
                'link' => $this->link ?? '',
                'file' => $this->updf = $this->handlePdfUpload(),
                'updated_at' => now(),
            ]);
            $this->reset('pdf');
            Toaster::success('Succesfully update factsheet');
        }
    }

    public function render()
    {
        return view('livewire.edit-factsheet-component');
    }

    protected function handlePdfUpload(){
        if (! $this->pdf) {
            return $this->updf;
        }

        // Tulis dulu, hapus belakangan: kalau store() gagal, berkas lama tetap ada.
        // Disk eksplisit: disk sementara Livewire berbeda saat pengujian.
        $this->pdf->store('public/files/factsheet', 'local');
        $new = $this->pdf->hashName();

        if ($this->updf && $this->updf !== $new) {
            Storage::delete('public/files/factsheet/'.$this->updf);
        }

        return $new;
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
        }elseif($this->link == '' && ! $this->pdf && ! $this->updf ){
            Toaster::error('Upload a PDF or fill the link!');
            return;
        }elseif($this->pdf && ! $this->pdfValid($this->pdf) ){
            return;
        }
        return true;
    }
}
