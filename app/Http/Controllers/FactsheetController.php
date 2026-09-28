<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FactsheetController extends Controller
{
    public function index(){
        $title = 'MapBiomas Fire - factsheet';
        $nav = 'factsheet';
        return view('backends.factsheet', compact('title', 'nav'));
    }

    public function add(){
        $title = 'MapBiomas Fire - add factsheet';
        $nav = 'factsheet';
        return view('backends.addfactsheet', compact('title', 'nav'));
    }

    public function edit($id){
        $title = 'MapBiomas Fire - edit factsheet';
        $nav = 'factsheet';
        $idFactsheet = $id;
        return view('backends.editfactsheet', compact('title', 'nav', 'idFactsheet'));
    }

    public function getSelect(){
        if (App::getLocale() == 'id') {
            return 'id, linkID as link, fileID as file, titleID as title, descriptionID as description';
        }else{
            return 'id, linkEN as link, fileEN as file, titleEN as title, descriptionEN as description';
        }
    }

    public function listFactsheet(){
        $title = 'MapBiomas Fire - Factsheet';
        $description = "Inisiatif MapBiomas Fire dimulai sejak 2023, bersama sembilan jaringan organisasi masyarakat sipil (CSO) yang dikoordinasi oleh Auriga Nusantara dan Woods and Wayside International (WWI). MapBiomas Fire memetakan kebakaran menggunakan teknologi komputasi yang didukung algoritma machine learning dan deep learning.";
        // Satu daftar gabungan annual + monthly; kategorinya ditampilkan
        // sebagai badge per item, bukan tab terpisah.
        $sheets = DB::table('factsheet')
                ->selectRaw($this->getSelect().', category')
                ->orderBy('category')
                ->orderByDesc('created_at')
                ->get();
        return view('frontends.factsheet', compact('title', 'description', 'sheets'));
    }

    /**
     * Melayani PDF factsheet satu origin untuk sampul PDF.js.
     * Berkas unggahan dilayani langsung; tautan luar di-proxy karena
     * peramban memblokir fetch lintas-domain (CORS).
     */
    public function file(Request $request, $id){
        $suffix = $request->query('lang') === 'en' ? 'EN' : 'ID';
        $row = DB::table('factsheet')->where('id', $id)->first();
        abort_if(! $row, 404);

        $fileCol = 'file'.$suffix;
        $linkCol = 'link'.$suffix;

        if (! empty($row->$fileCol)) {
            $path = storage_path('app/public/files/factsheet/'.$row->$fileCol);
            if (is_file($path)) {
                return response()->file($path, [
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }

        $link = $row->$linkCol ?? '';
        if (is_string($link) && str_starts_with($link, 'http')) {
            $resp = Http::timeout(60)->get($link);
            abort_if(! $resp->successful(), 404);
            return response($resp->body(), 200, [
                'Content-Type' => $resp->header('Content-Type', 'application/pdf'),
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        abort(404);
    }
}
