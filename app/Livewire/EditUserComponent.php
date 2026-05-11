<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class EditUserComponent extends Component
{
    public $email, $name, $password, $contact, $level, $idUser, $is_active;
    public $userPassword;

    public function mount($id){
        $this->idUser = $id;
        $data = DB::table('users')->where('id', $id)->first();
        $this->email = $data->email;
        $this->name = $data->name;
        $this->userPassword = $data->password;
        $this->contact = $data->contact;
        $this->level = $data->role_id;
        $this->is_active = $data->is_active;
    }
    public function render()
    {
        return view('livewire.edit-user-component');
    }

    public function storeUser(){
        if($this->manualValidation()){
            if(!$this->password){
                DB::table('users')
                ->where('id', $this->idUser)
                ->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'contact' => $this->contact,
                    'role_id' => $this->level,
                    'is_active' => $this->is_active,
                    'updated_at' => Carbon::now('Asia/Jakarta')
            ]);
            }else{
                DB::table('users')
                ->where('id', $this->idUser)
                ->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'contact' => $this->contact,
                    'password' => Hash::make($this->password),
                    'role_id' => $this->level,
                    'is_active' => $this->is_active,
                    'updated_at' => Carbon::now('Asia/Jakarta')
            ]);
            }

            redirect()->to('/users');
        }
    }

    public function manualValidation(){
        if($this->name == ''){
            Toaster::error('Name is required!');
            return;
        }elseif($this->email == ''){
            Toaster::error('Email is required!');
            return;
        }elseif($this->contact == ''){
            Toaster::error('Contact is required!');
            return;
        }elseif($this->level == ''){
            Toaster::error('Level is required!');
            return;
        }
        return true;
    }
}
