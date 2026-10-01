<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class AddUserComponent extends Component
{
    public $email, $name, $password, $contact;

    public $level = '';

    public function mount()
    {
        // the page route is admin-only, but Livewire actions post to their own endpoint
        abort_unless((int) session('role_id') === 0, 403);
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'contact' => 'required|string|max:30',
            'level' => 'required|in:0,1,2',
            'password' => 'required|string|min:8',
        ];
    }

    protected $messages = [
        'level.required' => 'Pick a role.',
        'email.unique' => 'Another user already has this email.',
    ];

    public function render()
    {
        return view('livewire.user-form', ['editing' => false, 'isSelf' => false]);
    }

    public function storeUser()
    {
        abort_unless((int) session('role_id') === 0, 403);
        $this->validate();

        DB::table('users')->insert([
            'name' => $this->name,
            'email' => $this->email,
            'contact' => $this->contact,
            'role_id' => $this->level,
            'is_active' => 1,
            'password' => Hash::make($this->password),
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);

        Toaster::success($this->name.' added');
        $this->redirect('/users');
    }
}
