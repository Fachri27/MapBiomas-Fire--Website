<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class PageAtbd extends Component
{
    public $contentEN, $contentID, $category;

    public function mount(){
        $cat = request()->query('cat');
        $this->category = in_array($cat, ['monthly', 'annual']) ? $cat : 'monthly';

        $data = DB::table('pageatbd')->where('category', $this->category)->first();
        if($data){
            $this->contentEN = $data->contentEN;
            $this->contentID = $data->contentID;
        }else{
            $this->contentEN = '';
            $this->contentID = '';
        }
    }

    // Full page reload on category switch: the TinyMCE editors are
    // wire:ignore so they must be re-initialized with the new content.
    // request()->url() is /livewire/update during a round-trip,
    // so target the page route explicitly.
    public function updatedCategory($value){
        redirect()->to(url('/cms/cmsatbd').'?cat='.$value);
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
