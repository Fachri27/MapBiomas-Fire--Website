<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class FactsheetController extends Controller
{
    public function index(){
        $title = 'MapBiomas Fire - fact sheet';
        $nav = 'fact sheet';
        return view('backends.factsheet', compact('title', 'nav'));
    }

    public function add(){
        $title = 'MapBiomas Fire - add fact sheet';
        $nav = 'fact sheet';
        return view('backends.addfactsheet', compact('title', 'nav'));
    }

    public function edit($id){
        $title = 'MapBiomas Fire - edit fact sheet';
        $nav = 'fact sheet';
        $idFactsheet = $id;
        return view('backends.editfactsheet', compact('title', 'nav', 'idFactsheet'));
    }

    public function getSelect(){
        if (App::getLocale() == 'id') {
            return 'id, link, titleID as title, descriptionID as description';
        }else{
            return 'id, link, titleEN as title, descriptionEN as description';
        }
    }

    public function listFactsheet(Request $request){
        $cat = $request->query('cat');
        $category = in_array($cat, ['monthly', 'annual']) ? $cat : 'monthly';

        $title = 'MapBiomas Fire - Fact Sheet';
        $description = "Inisiatif MapBiomas Fire dimulai sejak 2023, bersama sembilan jaringan organisasi masyarakat sipil (CSO) yang dikoordinasi oleh Auriga Nusantara dan Woods and Wayside International (WWI). MapBiomas Fire memetakan kebakaran menggunakan teknologi komputasi yang didukung algoritma machine learning dan deep learning.";
        $sheets = DB::table('factsheet')
                ->selectRaw($this->getSelect())
                ->where('category', $category)
                ->orderByDesc('created_at')
                ->get();
        return view('frontends.factsheet', compact('title', 'description', 'sheets', 'category'));
    }
}
