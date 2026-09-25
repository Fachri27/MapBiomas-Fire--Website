<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Masmerise\Toaster\Toaster;

class EditInfographicComponent extends Component
{
    use WithFileUploads;
    public $publishdate, $titleID, $titleEN, $photoID, $photoEN, $isactive, $descriptionID, $descriptionEN;

    #[Locked]
    public $idInfographic;

    #[Locked]
    public $uphotoID;

    #[Locked]
    public $uphotoEN;

    public $period;
    public $category;

    public function mount($id){
        $this->idInfographic = $id;
        $data = DB::table('infographic')->where('id', $id)->first();
        $this->publishdate = $data->publishdate;
        $this->period = $data->period;
        $this->category = $data->category;
        $this->titleEN = $data->titleEN;
        $this->titleID = $data->titleID;
        $this->descriptionID = $data->descriptionID;
        $this->descriptionEN = $data->descriptionEN;
        $this->isactive = $data->status;
        $this->uphotoEN = $data->imgEN;
        $this->uphotoID = $data->imgID;
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

    public function manualValidation(){
        if($this->titleID == '' ){
            Toaster::error('Title Indonesia is required!');
            return false;
        }elseif($this->titleEN == '' ){
            Toaster::error('Title English is required!');
            return false;
        }elseif($this->descriptionEN == '' ){
            Toaster::error('Description English is required!');
            return false;
        }elseif($this->descriptionID == '' ){
            Toaster::error('Description Indonesia is required!');
            return false;
        }elseif($this->publishdate == '' ){
            Toaster::error('Publish date is required!');
            return false;
        }elseif($this->photoID && ! $this->photoValid($this->photoID, 'photoID', 'Image (Indonesia)')){
            return false;
        }elseif($this->photoEN && ! $this->photoValid($this->photoEN, 'photoEN', 'Image (English)')){
            return false;
        }
        return true;
    }

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

    protected function handlePhotoUpload($newPhoto, $existingPhoto, $uploadMethod){
        if (!$newPhoto) {
            return $existingPhoto;
        }

        if ($existingPhoto) {
            Storage::delete([
                'public/files/photos/' . basename($existingPhoto),
                'public/files/photos/thumbnail/' . basename($existingPhoto)
            ]);
        }

        return $this->$uploadMethod();
    }

    public function storePosts(){
        if($this->manualValidation()){

            DB::table('infographic')
            ->where('id', $this->idInfographic)
            ->update([
                'publishdate' => $this->publishdate,
                'period' => $this->period ?: null,
                'category' => $this->category,
                'titleID' => $this->titleID,
                'titleEN' => $this->titleEN,
                'descriptionID' => $this->descriptionID,
                'descriptionEN' => $this->descriptionEN,
                'imgID' => $nameID = $this->handlePhotoUpload($this->photoID, $this->uphotoID, 'uploadImageID'),
                'imgEN' => $nameEN = $this->handlePhotoUpload($this->photoEN, $this->uphotoEN, 'uploadImageEN'),
                'status' => $this->isactive,
                'updated_at' => Carbon::now('Asia/Jakarta')
            ]);

            redirect()->to('/cms/listinfographic');
        }
    }
    public function render()
    {
        return view('livewire.edit-infographic-component');
    }
}
