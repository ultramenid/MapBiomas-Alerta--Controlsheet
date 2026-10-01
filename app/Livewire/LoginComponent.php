<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Masmerise\Toaster\Toaster;


class LoginComponent extends Component
{


    public $email, $password;

    public function getDatauser(){
        return DB::table('users')->where('email', $this->email)->where('is_active', 1)->first();
    }

    public function login(){
        // dd($this->getDatauser());
        $this->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $key = 'login:'.strtolower((string) $this->email).'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            Toaster::error('Too many attempts. Try again in '.RateLimiter::availableIn($key).' seconds.');
            return;
        }

         //log in logic
         $user = $this->getDatauser();
         if($user and Hash::check($this->password, $user->password ) and $this->email == $user->email) {
            RateLimiter::clear($key);
            session()->regenerate();
            session([
                'id' => $user->id,
                'role_id'=> $user->role_id,
                'name' => $user->name,
                'email' => $user->email,
                'yearAlert' => 'all'
            ]);
            redirect('/dashboard');
         }else{
            RateLimiter::hit($key, 60);
            Toaster::error('email & Password not valid.');
         }
    }
    public function render()
    {
        return view('livewire.login-component');
    }
}
