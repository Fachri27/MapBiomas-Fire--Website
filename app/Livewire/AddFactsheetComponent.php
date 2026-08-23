<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class AddFactsheetComponent extends Component
{
    public $category, $titleEN, $titleID, $descriptionEN, $descriptionID, $link;

    public function storeAksi(){
        if($this->manualValidation()){
            DB::table('factsheet')->insert([
                'category' => $this->category,
                'titleID' => $this->titleID,
                'titleEN' => $this->titleEN,
                'descriptionID' => $this->descriptionID,
                'descriptionEN' => $this->descriptionEN,
                'link' => $this->link,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        redirect()->to('/cms/listfactsheet');
    }

    public function render()
    {
        return view('livewire.add-factsheet-component');
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
        }elseif($this->link == '' ){
            Toaster::error('Link is required!');
            return;
        }
        return true;
    }
}
