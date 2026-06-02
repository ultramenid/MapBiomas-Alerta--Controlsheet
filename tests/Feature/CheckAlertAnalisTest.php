<?php

use App\Livewire\CheckAlertAnalis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('does not restrict alert status by validator counts to the date range picker', function () {
    insertValidator();
    insertAlert(detectionDate: '2024-01-15');

    $component = new CheckAlertAnalis();
    $component->mount();
    $component->yearAlert = 'all';

    $alerts = $component->getAlerts();

    expect($alerts)->toHaveCount(1)
        ->and($alerts->first()->name)->toBe('Validator One')
        ->and((int) $alerts->first()->approved)->toBe(1)
        ->and((int) $alerts->first()->total)->toBe(1);
});

it('filters alert status by validator counts by dashboard year', function () {
    insertValidator();
    insertAlert(alertId: 9001, detectionDate: '2024-01-15');
    insertAlert(alertId: 9002, detectionDate: '2025-01-15');

    $component = new CheckAlertAnalis();
    $component->mount();
    $component->updateData('2024');

    $alerts = $component->getAlerts();

    expect($alerts)->toHaveCount(1)
        ->and((int) $alerts->first()->approved)->toBe(1)
        ->and((int) $alerts->first()->total)->toBe(1);
});

function insertValidator(): void
{
    DB::table('users')->insert([
        'id' => 101,
        'name' => 'Validator One',
        'email' => 'validator-one@example.test',
        'password' => 'password',
        'contact' => '08123456789',
        'role_id' => 2,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function insertAlert(int $alertId = 9001, string $detectionDate = '2024-01-15'): void
{
    DB::table('alerts')->insert([
        'alertId' => $alertId,
        'analisId' => 101,
        'alertStatus' => 'approved',
        'detectionDate' => $detectionDate,
        'observation' => 'Observation',
        'region' => 'Java',
        'province' => 'West Java',
        'auditorStatus' => 'approved',
        'isActive' => 1,
        'platformStatus' => 'sccon',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
