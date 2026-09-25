<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Masmerise\Toaster\Toaster;

class EditNewsComponent extends Component
{
    use WithFileUploads;

    #[Locked]
    public $idNews;

    #[Locked]
    public $uphoto;

    public $publishdate, $titleID, $titleEN, $descriptionID, $descriptionEN, $contentID, $contentEN, $photo, $isactive, $category;

    public function mount($id){
        $data = DB::table('news')->where('id', $id)->first();
        $this->idNews = $id;
        $this->publishdate = $data->publishdate;
        $this->titleID = $data->titleID;
        $this->titleEN = $data->titleEN;
        $this->descriptionEN = $data->descriptionEN;
        $this->descriptionID = $data->descriptionID;
        $this->contentID = $data->contentID;
        $this->contentEN = $data->contentEN;
        $this->isactive = $data->status;
        $this->uphoto = $data->img;
        $this->category = $data->category;


    }

    public function uploadImage(){
        $this->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $file = $this->photo->store('public/files/photos');
        $foto = $this->photo->hashName();

        // Folder thumbnail dibuat bila belum ada agar penyimpanan tidak gagal.
        if (!is_dir('storage/files/photos/thumbnail')) {
            mkdir('storage/files/photos/thumbnail', 0775, true);
        }

        $manager = new ImageManager(new Driver());

        // https://image.intervention.io/v3/modifying/resizing
        $image = $manager->read('storage/files/photos/'.$foto)->cover(300, 150);
        $image->save('storage/files/photos/thumbnail/'.$foto);
        return $foto;
    }



    public function storePosts(){
        if($this->manualValidation()){
            if(!$this->photo){
                $name = $this->uphoto;
            }else{
                $this->validate([
                    'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
                ]);
                $cleanPhoto = $this->uphoto ? basename($this->uphoto) : null;
                if ($cleanPhoto && ! in_array($cleanPhoto, ['.', '..'])) {
                    Storage::delete('public/files/photos/'.$cleanPhoto);
                    Storage::delete('public/files/photos/thumbnail/'.$cleanPhoto);
                }
                $name=  $this->uploadImage();
            }
            DB::table('news')
                    ->where('id', $this->idNews)
                    ->update([
                        'publishdate' => $this->publishdate,
                        'titleID' => $this->titleID,
                        'titleEN' => $this->titleEN,
                        'descriptionID' => $this->descriptionID,
                        'descriptionEN' => $this->descriptionEN,
                        'contentID' => $this->contentID,
                        'contentEN' => $this->contentEN,
                        'category' => $this->category,
                        'img' => $name,
                        'status' => $this->isactive,
                        'updated_at' => Carbon::now('Asia/Jakarta')
                    ]);

            $this->uphoto = $name;
            $this->reset('photo');

            Toaster::success('Succesfully update news');
        }

    }
    public function render()
    {
        return view('livewire.edit-news-component');
    }

    public function manualValidation(){
        if(strlen($this->titleID) > 120){
            Toaster::error('Title Indonesia limit 120 character!');
            return;
        }elseif($this->titleID == '' ){
            Toaster::error('Title Indonesia is required!');
            return;
        }elseif(strlen($this->titleEN) > 120){
            Toaster::error('Title English limit 120 character!');
            return;
        }elseif($this->titleEN == '' ){
            Toaster::error('Title English is required!');
            return;
        }

        if ($this->photo) {
            $validator = Validator::make(
                ['photo' => $this->photo],
                ['photo' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120']]
            );

            if ($validator->fails()) {
                $this->addError('photo', $validator->errors()->first('photo'));
                Toaster::error($validator->errors()->first('photo'));
                return;
            }
        }

        if($this->descriptionID == '' ){
            Toaster::error('Description Indonesia is required!');
            return;
        }elseif(strlen($this->descriptionID) > 255 ){
            Toaster::error('Description Indonesia limit 255 character!');
            return;
        }elseif($this->descriptionEN == '' ){
            Toaster::error('Description English is required!');
            return;
        }elseif(strlen($this->descriptionEN) > 255 ){
            Toaster::error('Description English limit 255 character!');
            return;
        }elseif($this->contentID == '' ){
            Toaster::error('Content Indonesia is required!');
            return;
        }elseif($this->publishdate == '' ){
            Toaster::error('Publish date is required!');
            return;
        }elseif($this->category == '' ){
            Toaster::error('Category is required!');
            return;
        }
        return true;
    }
}
