<?php

namespace App\Livewire;

use App\Support\Turnstile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class LoginComponent extends Component
{
    public $email, $password;

    /** Diisi dari peramban lewat callback widget Turnstile. */
    public $turnstileToken = '';

    public function getDatauser(){
        return DB::table('users')->where('email', $this->email)->first();
    }
    public function login(){
        // dd($this->getDatauser());
        $this->validate([
            'email' => 'required',
            'password' => 'required'
        ]);
        // Captcha diperiksa lebih dulu, sebelum kata sandi disentuh: percobaan
        // otomatis ditolak tanpa sempat menebak-nebak kredensial.
        if(! Turnstile::verify($this->turnstileToken, request()->ip())) {
            $this->resetTurnstile();
            session()->flash('message', 'Verifikasi captcha gagal. Silakan coba lagi.');
            return;
        }

        //log in logic
        if($this->getDatauser() and Hash::check($this->password, $this->getDatauser()->password ) and $this->email == $this->getDatauser()->email) {
           session([
               'id' => $this->getDatauser()->id,
               'role_id'=> $this->getDatauser()->role_id
           ]);
        //    dd('oke');
           redirect('/cms/dashboard');
        //    $this->redirect('/cms/dashboard', navigate: true);

        }else{
            $this->resetTurnstile();
            session()->flash('message', 'email & Password not valid.');
        }
    }

    /**
     * Token Turnstile sekali pakai. Setelah percobaan gagal, widget harus
     * diminta menerbitkan token baru — kalau tidak, percobaan berikutnya
     * selalu ditolak meski kata sandinya sudah benar.
     */
    private function resetTurnstile(): void
    {
        $this->turnstileToken = '';
        $this->dispatch('turnstile-reset');
    }
    public function render()
    {
        return view('livewire.login-component');
    }
}
