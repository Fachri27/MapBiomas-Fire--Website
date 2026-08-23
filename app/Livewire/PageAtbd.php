<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class PageAtbd extends Component
{
    public const KATEGORI = ['monthly', 'annual'];

    public $contentEN, $contentID;

    /** Disinkronkan ke ?cat= lewat history, tanpa memuat ulang halaman. */
    #[Url(as: 'cat')]
    public $category = 'monthly';

    public function mount(){
        if(! in_array($this->category, self::KATEGORI)){
            $this->category = 'monthly';
        }

        $this->muatKonten();
    }

    /**
     * Dulu ini memuat ulang seluruh halaman, yang berarti kedua editor TinyMCE
     * ikut dibangun ulang — itu sumber lambatnya. Sekarang cukup satu permintaan
     * kecil: kontennya diambil lalu didorong ke editor yang sudah berdiri.
     */
    public function updatedCategory($value){
        if(! in_array($value, self::KATEGORI)){
            $this->category = 'monthly';
        }

        $this->muatKonten();

        $this->dispatch(
            'atbd-konten-diganti',
            contentEN: $this->contentEN,
            contentID: $this->contentID,
        );
    }

    private function muatKonten(): void
    {
        $data = DB::table('pageatbd')->where('category', $this->category)->first();

        $this->contentEN = $data->contentEN ?? '';
        $this->contentID = $data->contentID ?? '';
    }

    public function storePage(){
        if($this->manualValidation()){
            DB::table('pageatbd')
            ->updateOrInsert(
                ['category' => $this->category],
                [
                    'name' => 'atbd-'.$this->category,
                    'contentEN' => $this->contentEN,
                    'contentID' => $this->contentID,
                ]
            );
        //passing to toast
        Toaster::success('Succesfully update page ATBD '.$this->category);
        }
    }
    public function render()
    {
        return view('livewire.page-atbd');
    }

    public function manualValidation(){
        if($this->contentEN == ''){
            Toaster::error('Content english is required');

            return;

        }elseif($this->contentID == ''){
            Toaster::error('Content indonesia is required');
            return;
        }
        return true;
    }
}
