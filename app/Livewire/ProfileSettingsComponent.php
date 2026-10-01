<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class ProfileSettingsComponent extends Component
{
    public $name, $contact;

    // email signs you in and role sets access, so only an admin changes them (Users page)
    #[Locked]
    public $email, $role, $createdAt;

    public function mount()
    {
        $user = DB::table('users')->where('id', session('id'))->first();
        abort_unless($user, 403);

        $this->name = $user->name;
        $this->contact = $user->contact;
        $this->email = $user->email;
        $this->role = ['Admin', 'Auditor', 'Validator'][(int) $user->role_id] ?? 'Unknown';
        $this->createdAt = $user->created_at;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:30',
        ];
    }

    public function saveProfile()
    {
        $this->validate();

        DB::table('users')->where('id', session('id'))->update([
            'name' => $this->name,
            'contact' => $this->contact,
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ]);
        // the header and the online list read the name from the session
        session(['name' => $this->name]);

        Toaster::success('Profile saved');
        $this->redirect('/settings');
    }

    public function render()
    {
        return view('livewire.profile-settings-component');
    }
}
