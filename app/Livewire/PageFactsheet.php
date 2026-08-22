<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class PageFactsheet extends Component
{
    public $titleEN, $titleID, $descriptionEN, $descriptionID, $link, $category;

    public function mount(){
        $cat = request()->query('cat');
        $this->category = in_array($cat, ['monthly', 'annual']) ? $cat : 'monthly';

        $data = DB::table('pagefactsheet')->where('category', $this->category)->first();
        if($data){
            $this->titleEN = $data->titleEN;
            $this->titleID = $data->titleID;
            $this->descriptionEN = $data->descriptionEN;
            $this->descriptionID = $data->descriptionID;
            $this->link = $data->link;
        }else{
            $this->titleEN = '';
            $this->titleID = '';
            $this->descriptionEN = '';
            $this->descriptionID = '';
            $this->link = '';
        }
    }

    // Full page reload on category switch: request()->url() is
    // /livewire/update during a round-trip, so target the page
    // route explicitly.
    public function updatedCategory($value){
        redirect()->to(url('/cms/cmsfactsheet').'?cat='.$value);
    }

    public function storePage(){
        if($this->manualValidation()){
            DB::table('pagefactsheet')
            ->updateOrInsert(
                ['category' => $this->category],
                [
                    'name' => 'factsheet-'.$this->category,
                    'titleEN' => $this->titleEN,
                    'titleID' => $this->titleID,
                    'descriptionEN' => $this->descriptionEN,
                    'descriptionID' => $this->descriptionID,
                    'link' => $this->link,
                ]
            );
        //passing to toast
        Toaster::success('Succesfully update page fact sheet '.$this->category);
        }
    }
    public function render()
    {
        return view('livewire.page-factsheet');
    }

    public function manualValidation(){
        if($this->titleEN == ''){
            Toaster::error('Title english is required');
            return;
        }elseif($this->titleID == ''){
            Toaster::error('Title indonesia is required');
            return;
        }elseif($this->descriptionEN == ''){
            Toaster::error('Description english is required');
            return;
        }elseif($this->descriptionID == ''){
            Toaster::error('Description indonesia is required');
            return;
        }elseif($this->link == ''){
            Toaster::error('Link is required');
            return;
        }
        return true;
    }
}
