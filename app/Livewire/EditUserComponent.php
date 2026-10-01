<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class EditUserComponent extends Component
{
    public $email, $name, $password, $contact, $level, $is_active;

    #[Locked]
    public $idUser;

    #[Locked]
    public $createdAt;

    public function mount($id)
    {
        // the page route is admin-only, but Livewire actions post to their own endpoint
        abort_unless((int) session('role_id') === 0, 403);

        $data = DB::table('users')->where('id', $id)->first();
        abort_unless($data, 404);

        $this->idUser = $data->id;
        $this->email = $data->email;
        $this->name = $data->name;
        $this->contact = $data->contact;
        $this->level = (string) $data->role_id;
        $this->is_active = (string) (int) $data->is_active;
        $this->createdAt = $data->created_at;
    }

    protected function isSelf()
    {
        return (int) $this->idUser === (int) session('id');
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->idUser)],
            'contact' => 'required|string|max:30',
            // an admin editing themselves can't drop their own admin role or access
            'level' => $this->isSelf() ? 'required|in:0' : 'required|in:0,1,2',
            'is_active' => $this->isSelf() ? 'required|in:1' : 'required|in:0,1',
            'password' => 'nullable|string|min:8',
        ];
    }

    protected $messages = [
        'level.in' => 'You cannot change your own role.',
        'is_active.in' => 'You cannot deactivate your own account.',
        'email.unique' => 'Another user already has this email.',
    ];

    public function render()
    {
        return view('livewire.user-form', ['editing' => true, 'isSelf' => $this->isSelf()]);
    }

    public function storeUser()
    {
        abort_unless((int) session('role_id') === 0, 403);
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'contact' => $this->contact,
            'role_id' => $this->level,
            'is_active' => $this->is_active,
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ];
        if (filled($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        DB::table('users')->where('id', $this->idUser)->update($data);

        Toaster::success($this->name.' saved');
        $this->redirect('/users');
    }
}
