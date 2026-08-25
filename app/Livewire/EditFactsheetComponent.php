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

    public $category, $titleEN, $titleID, $descriptionEN, $descriptionID, $idFactsheet;
    // Unduhan ikut bahasa: tiap edisi punya PDF/tautannya sendiri.
    public $linkID, $linkEN, $pdfID, $pdfEN, $updfID, $updfEN;

    public function mount($id){
        $data = DB::table('factsheet')->where('id', $id)->first();
        $this->category = $data->category;
        $this->titleID = $data->titleID;
        $this->titleEN = $data->titleEN;
        $this->descriptionID = $data->descriptionID;
        $this->descriptionEN = $data->descriptionEN;
        $this->linkID = $data->linkID;
        $this->linkEN = $data->linkEN;
        $this->updfID = $data->fileID;
        $this->updfEN = $data->fileEN;
        $this->idFactsheet = $id;
    }

    public function updated($name, $value){
        if (in_array($name, ['pdfID', 'pdfEN']) && ! $this->pdfValid($value)) {
            $this->reset($name);
        }
    }

    public function storeAksi(){
        if($this->manualValidation()){
            // Hitung keduanya dulu, baru simpan: berkas lama masih dibutuhkan
            // sebagai pembanding saat memutuskan penghapusan.
            $fileID = $this->handlePdfUpload($this->pdfID, $this->updfID, $this->updfEN);
            $fileEN = $this->handlePdfUpload($this->pdfEN, $this->updfEN, $this->updfID);

            DB::table('factsheet')->where('id', $this->idFactsheet)->update([
                'category' => $this->category,
                'titleID' => $this->titleID,
                'titleEN' => $this->titleEN,
                'descriptionID' => $this->descriptionID,
                'descriptionEN' => $this->descriptionEN,
                'linkID' => $this->linkID ?? '',
                'linkEN' => $this->linkEN ?? '',
                'fileID' => $this->updfID = $fileID,
                'fileEN' => $this->updfEN = $fileEN,
                'updated_at' => now(),
            ]);
            $this->reset('pdfID', 'pdfEN');
            Toaster::success('Succesfully update factsheet');
        }
    }

    public function render()
    {
        return view('livewire.edit-factsheet-component');
    }

    /**
     * $lama  berkas bahasa ini sekarang
     * $lain  berkas bahasa satunya; entri warisan memakai berkas yang sama di
     *        kedua bahasa, jadi jangan hapus selama sisi lain masih memakainya.
     */
    protected function handlePdfUpload($pdf, $lama, $lain){
        if (! $pdf) {
            return $lama;
        }

        // Tulis dulu, hapus belakangan: kalau store() gagal, berkas lama tetap ada.
        // Disk eksplisit: disk sementara Livewire berbeda saat pengujian.
        $pdf->store('public/files/factsheet', 'local');
        $new = $pdf->hashName();

        if ($lama && $lama !== $new && $lama !== $lain) {
            Storage::delete('public/files/factsheet/'.$lama);
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
        }elseif($this->linkID == '' && ! $this->pdfID && ! $this->updfID ){
            Toaster::error('Upload a PDF or fill the link for indonesia!');
            return;
        }elseif($this->linkEN == '' && ! $this->pdfEN && ! $this->updfEN ){
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
