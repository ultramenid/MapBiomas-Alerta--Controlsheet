<?php

use App\Support\Html;
use UniSharp\LaravelFilemanager\Events\FileIsRenaming;
use UniSharp\LaravelFilemanager\Events\FileIsUploading;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('keeps non-admins out of user management', function () {
    $this->withSession(['id' => 5, 'role_id' => 2])->get('/users')->assertRedirect('/dashboard');
    $this->withSession(['id' => 5])->get('/adduser')->assertRedirect('/dashboard');
    $this->flushSession()->get('/edituser/1')->assertRedirect('/');
});

it('strips script from rich-text notes but keeps formatting and images', function () {
    $out = Html::clean('<p onclick="x()"><b>ok</b><img src="/storage/photos/a.png" onerror="alert(1)"><script>alert(1)</script></p>');
    expect($out)->toContain('<b>ok</b>')->toContain('/storage/photos/a.png')
        ->not->toContain('script')->not->toContain('onerror')->not->toContain('onclick');
});

it('blocks filemanager renames and uploads to executable names', function (object $event) {
    expect(fn () => event($event))->toThrow(HttpException::class);
})->with([
    'rename extensionless to php' => fn () => new FileIsRenaming('/s/photos/Screenshot', '/s/photos/Screenshot.x.php'),
    'upload phar' => fn () => new FileIsUploading('/s/files/lol3.phar'),
    'upload no extension' => fn () => new FileIsUploading('/s/files/shell'),
]);

it('allows normal filemanager files', function () {
    event(new FileIsUploading('/s/photos/map.PNG'));
    event(new FileIsRenaming('/s/files/a.pdf', '/s/files/report.pdf'));
    expect(true)->toBeTrue();
});

it('re-applies route guards to livewire actions', function () {
    $this->artisan('migrate');
    $html = $this->withSession(['id' => 1, 'role_id' => 0])->get('/users')->assertOk()->getContent();
    $snapshot = htmlspecialchars_decode(str($html)->betweenFirst('wire:snapshot="', '"'));

    $this->flushSession()->withSession(['id' => 5, 'role_id' => 2])
        ->withHeaders(['X-Livewire' => '1'])
        ->postJson('/livewire/update', ['_token' => csrf_token(), 'components' => [
            ['snapshot' => $snapshot, 'updates' => [], 'calls' => [['path' => '', 'method' => 'deleting', 'params' => [1]]]],
        ]])
        ->assertRedirect('/dashboard');
});

it('signs the online presence channel only for logged-in users, as themselves', function () {
    ['key' => $key, 'secret' => $secret] = config('broadcasting.connections.reverb');

    $this->post('/online/auth', ['socket_id' => '1.1'])->assertRedirect('/');

    $data = '{"user_id":"5","user_info":{"name":"Ani"}}';
    $this->withSession(['id' => 5, 'name' => 'Ani'])->post('/online/auth', ['socket_id' => '1.1', 'channel_name' => 'private-other'])
        ->assertOk()
        ->assertExactJson(['auth' => $key.':'.hash_hmac('sha256', "1.1:presence-online:$data", $secret), 'channel_data' => $data]);
});
