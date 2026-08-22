<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class EditFactsheetComponent extends Component
{
    public $category, $titleEN, $titleID, $descriptionEN, $descriptionID, $link, $idFactsheet;

    public function mount($id){
        $data = DB::table('factsheet')->where('id', $id)->first();
        $this->category = $data->category;
        $this->titleID = $data->titleID;
        $this->titleEN = $data->titleEN;
        $this->descriptionID = $data->descriptionID;
        $this->descriptionEN = $data->descriptionEN;
        $this->link = $data->link;
        $this->idFactsheet = $id;
    }

    public function storeAksi(){
        if($this->manualValidation()){
            DB::table('factsheet')->where('id', $this->idFactsheet)->update([
                'category' => $this->category,
                'titleID' => $this->titleID,
                'titleEN' => $this->titleEN,
                'descriptionID' => $this->descriptionID,
                'descriptionEN' => $this->descriptionEN,
                'link' => $this->link,
                'updated_at' => now(),
            ]);
            Toaster::success('Succesfully update fact sheet');
        }
    }

    public function render()
    {
        return view('livewire.edit-factsheet-component');
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
