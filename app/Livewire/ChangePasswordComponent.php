<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class ChangePasswordComponent extends Component
{
    public $oldpassword, $newpassword, $newpassword_confirmation;

    protected function rules()
    {
        return [
            'oldpassword' => 'required',
            'newpassword' => 'required|string|min:8|confirmed|different:oldpassword',
        ];
    }

    protected $messages = [
        'oldpassword.required' => 'Enter your current password.',
        'newpassword.confirmed' => 'The two new passwords do not match.',
        'newpassword.different' => 'Pick a password different from the current one.',
    ];

    public function storePassword()
    {
        $this->validate();

        // same budget as the login form, so this can't be used to guess the current password
        $key = 'password-check:'.session('id');
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('oldpassword', 'Too many attempts. Try again in '.RateLimiter::availableIn($key).' seconds.');
            return;
        }

        $user = DB::table('users')->where('id', session('id'))->first();
        if (! $user || ! Hash::check($this->oldpassword, $user->password)) {
            RateLimiter::hit($key, 60);
            $this->addError('oldpassword', 'Current password is incorrect. Forgot it? Ask an admin to reset it.');
            return;
        }
        RateLimiter::clear($key);

        DB::table('users')->where('id', session('id'))->update([
            'password' => Hash::make($this->newpassword),
            'updated_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $this->reset('oldpassword', 'newpassword', 'newpassword_confirmation');
        Toaster::success('Password updated');
    }

    public function render()
    {
        return view('livewire.change-password-component');
    }
}
