<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class FrontendNews extends Component
{
    use WithPagination;

    public $paginate = 10, $category = 'all', $query = '';

    // Hook Livewire: dipicu wire:model.live pada select kategori.
    public function updatedCategory(){
        $this->resetPage();
    }

    public function getSelectNews()
    {
        if (app()->getLocale() == 'id') {
            return 'id, titleID as title, img, slug, category, publishdate, descriptionID as description';
        } else {
            return 'id, titleEN as title, img, slug, category, publishdate, descriptionEN as description';
        }
    }
    public function getNews()
    {
        $sc = '%' . $this->query . '%';
        $query = $this->getSelectNews();

        $newsQuery = DB::table('news')
        ->selectRaw($query)
        ->where('publishdate', '<', now())
        ->where('status', 1)
        ->where('titleID', 'like', $sc)
        ->when($this->category !== 'all', function ($query) {
            return $query->where('category', $this->category);
        });


        return $newsQuery->orderByDesc('publishdate')->paginate($this->paginate);
    }
    public function render()
    {
        $data = $this->getNews();
        return view('livewire.frontend-news', compact('data'));
    }
}
