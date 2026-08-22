<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FactsheetComponent extends Component
{
    public $deleteName, $deleteID, $deleter;

    public function closeDelete(){
        $this->deleter = false;
        $this->deleteName = null;
        $this->deleteID = null;
    }

    public function delete($id){
        //load data to delete function
        $dataDelete = DB::table('factsheet')->where('id', $id)->first();
        $this->deleteName = $dataDelete->titleEN;
        $this->deleteID = $dataDelete->id;

        $this->deleter = true;
    }

    public function storeDelete(){
        DB::table('factsheet')->where('id', $this->deleteID)->delete();
        $this->closeDelete();
    }

    public function render()
    {
        $posts = DB::table('factsheet')
                ->orderByDesc('created_at')
                ->paginate(10);

        return view('livewire.factsheet-component', ['posts' => $posts]);
    }
}
