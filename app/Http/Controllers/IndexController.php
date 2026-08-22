<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function index()
    {
        // Landing baru (porting dari proyek prototipe); kabar & infografis
        // diambil dari CMS seperti pada tampilan lama.
        return view('frontends.landing', [
            'news' => $this->getNews(),
            'infographic' => $this->getInfographic(),
        ]);
    }

    public function selectNews()
    {
        if (app()->getLocale() == 'id') {
            return 'id, titleID as title, descriptionID as description, img, slug, publishdate';
        } else {
            return 'id, titleEN as title, descriptionEN as description, img, slug, publishdate';
        }
    }

    public function getSelectInfographic()
    {
        if (app()->getLocale() == 'id') {
            return 'id, titleID as title, imgID as img, descriptionID as description, slug';
        } else {
            return 'id, titleEN as title, imgEN as img, descriptionEN as description, slug';
        }
    }

    public function getInfographic()
    {
        return DB::table('infographic')
            ->selectRaw($this->getSelectInfographic())
            ->where('publishdate', '<', Carbon::now('Asia/Jakarta'))
            ->where('status', 1)
            ->orderBy('publishdate', 'desc')
            ->first();
    }

    public function getNews()
    {
        return DB::table('news')
            ->selectRaw($this->selectNews())
            ->where('publishdate', '<', Carbon::now('Asia/Jakarta'))
            ->where('category', 'news')
            ->where('status', 1)
            ->orderBy('publishdate', 'desc')
            ->take(2)
            ->get();
    }

    public function getEvent()
    {
        return DB::table('news')
            ->selectRaw($this->selectNews())
            ->where('publishdate', '<', Carbon::now('Asia/Jakarta'))
            ->where('category', 'event')
            ->where('status', 1)
            ->orderBy('publishdate', 'desc')
            ->take(2)
            ->get();
    }
}
