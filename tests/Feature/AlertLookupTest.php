<?php

use App\Livewire\AlertLookupComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('does not query the int alertId column with a non-numeric needle', function () {
    // MySQL would cast 'qweqweqwe' to 0 and match the alertId = 0 row.
    $component = new AlertLookupComponent();
    $component->alertCode = 'qweqweqwe';

    DB::enableQueryLog();
    $component->find();
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($queries)->toBeEmpty()
        ->and($component->lookup['found'])->toBeFalse()
        ->and($component->lookup['code'])->toBe('qweqweqwe');
});

it('still looks up a numeric alert ID', function () {
    DB::table('users')->insert([
        'id' => 101, 'name' => 'Validator One', 'email' => 'validator-one@example.test',
        'password' => 'password', 'contact' => '08123456789', 'role_id' => 2,
        'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('alerts')->insert([
        'alertId' => 10002951, 'analisId' => 101, 'alertStatus' => 'valid',
        'detectionDate' => '2026-04-29', 'observation' => 'Observation', 'region' => 'Papua',
        'province' => 'South Papua', 'auditorStatus' => 'rejected', 'isActive' => 1,
        'platformStatus' => 'sccon', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $component = new AlertLookupComponent();
    $component->alertCode = '10002951';
    $component->find();

    expect($component->lookup['found'])->toBeTrue()
        ->and($component->lookup['validator']['name'])->toBe('Validator One');
});
