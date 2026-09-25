<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Masmerise\Toaster\Toaster;

class AddInfographicComponent extends Component
{
    use WithFileUploads;
    public $period;
    public $category = 'monthly';
    public $publishdate, $titleID, $titleEN, $descriptionID, $descriptionEN, $photoID, $photoEN, $isactive=0;

    public function uploadImageID(){
        $file = $this->photoID->store('public/files/photos', 'local');
        $foto = $this->photoID->hashName();
        return $foto;
    }
    public function uploadImageEN(){
        $file = $this->photoEN->store('public/files/photos', 'local');
        $foto = $this->photoEN->hashName();
        return $foto;
    }

    public function photoValid($file, $field = 'photoID', $label = 'Image'){
        if (! $file) {
            return false;
        }

        $validator = Validator::make(
            [$field => $file],
            [$field => 'file|mimes:jpeg,png,jpg,webp,gif,mp4,avi,mov,3gp,m4a|max:20480'],
            [],
            [$field => $label]
        );

        if ($validator->fails()) {
            $error = $validator->errors()->first($field);
            Toaster::error($error);
            $this->addError($field, $error);
            return false;
        }

        return true;
    }

    public function updatedPhotoID($photo){
        if ($photo && ! $this->photoValid($photo, 'photoID', 'Image (Indonesia)')) {
            $this->reset('photoID');
        }
    }
    public function updatedPhotoEN($photo){
        if ($photo && ! $this->photoValid($photo, 'photoEN', 'Image (English)')) {
            $this->reset('photoEN');
        }
    }
    public function storePosts(){
        if($this->manualValidation()){
            DB::table('infographic')->insert([
                'publishdate' => $this->publishdate,
                'period' => $this->period ?: null,
                'category' => $this->category,
                'titleID' => $this->titleID,
                'titleEN' => $this->titleEN,
                'slug' => Str::slug($this->titleID,'-'),
                'descriptionID' => $this->descriptionID,
                'descriptionEN' => $this->descriptionEN,
                'imgID' => $this->uploadImageID(),
                'imgEN' => $this->uploadImageEN(),
                'status' => $this->isactive,
                'created_at' => Carbon::now('Asia/Jakarta')
            ]);

            redirect()->to('/cms/listinfographic');
            // $this->redirect('/cms/listnews', navigate: true);
        }
    }
    public function render()
    {
        return view('livewire.add-infographic-component');
    }

    public function manualValidation(){
        if(!in_array($this->category, ['monthly', 'annual'])){
            Toaster::error('Category must be monthly or annual!');
            return false;
        }elseif($this->titleID == '' ){
            Toaster::error('Title Indonesia is required!');
            return false;
        }elseif($this->titleEN == '' ){
            Toaster::error('Title English is required!');
            return false;
        }elseif($this->photoID == '' ){
            Toaster::error('Image Indonesia is required!');
            return false;
        }elseif(! $this->photoValid($this->photoID, 'photoID', 'Image (Indonesia)')){
            return false;
        }elseif($this->photoEN == '' ){
            Toaster::error('Image English is required!');
            return false;
        }elseif(! $this->photoValid($this->photoEN, 'photoEN', 'Image (English)')){
            return false;
        }elseif($this->descriptionID == '' ){
            Toaster::error('Description Indonesia is required!');
            return false;
        }elseif($this->descriptionEN == '' ){
            Toaster::error('Description English is required!');
            return false;
        }elseif($this->publishdate == '' ){
            Toaster::error('Publish date is required!');
            return false;
        }
        return true;
    }
}
