<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * "Who handled this alert?" — one alert-ID lookup returning auditor + validator.
 *
 * Split out of WorkTrendChartComponent: wire:model.live re-renders on every
 * keystroke, and the chart card's brush slider is JS-built DOM that Livewire's
 * morph wipes. Isolating the search keeps the chart untouched while typing.
 */
class AlertLookupComponent extends Component
{
    public string $alertCode = '';
    public ?array $lookup = null;

    /** Live search: type the ID, no Enter needed. */
    public function updatedAlertCode(): void
    {
        $this->find();
    }

    public function find(): void
    {
        $code = trim($this->alertCode);
        if (strlen($code) < 3) {
            $this->lookup = null;
            return;
        }

        // ngapain='auditing' only — auditorlog also stores validator actions
        // (Insert/Reject/refined/reexportimage/reclassification), so without
        // this filter the latest row is often the validator, not the auditor.
        $auditor = DB::table('auditorlog')
            ->join('users', 'users.id', '=', 'auditorlog.auditorId')
            ->where('auditorlog.alertId', $code)
            ->where('auditorlog.ngapain', 'auditing')
            ->orderByDesc('auditorlog.created_at')
            ->select('users.name', 'users.id', 'auditorlog.created_at')
            ->first();

        $alert = DB::table('alerts')
            ->leftJoin('users', 'users.id', '=', 'alerts.analisId')
            ->where('alerts.alertId', $code)
            ->where('alerts.isActive', 1)
            ->select(
                'users.name as vName',
                'users.id as vId',
                'alerts.auditorStatus',
                'alerts.updated_at',
                'alerts.region',
                'alerts.province',
                'alerts.detectionDate',
                'alerts.alertStatus'
            )
            ->first();

        // full trail, so "who handled this" is answerable beyond the headline
        $history = DB::table('auditorlog')
            ->leftJoin('users', 'users.id', '=', 'auditorlog.auditorId')
            ->where('auditorlog.alertId', $code)
            ->orderByDesc('auditorlog.created_at')
            ->limit(10)
            ->select('users.name', 'auditorlog.ngapain', 'auditorlog.created_at')
            ->get()
            ->map(fn ($r) => ['name' => $r->name ?? 'Unknown', 'action' => $r->ngapain, 'at' => $r->created_at])
            ->all();

        $this->lookup = [
            'code' => $code,
            'auditor' => $auditor ? ['name' => $auditor->name, 'id' => $auditor->id, 'at' => $auditor->created_at] : null,
            'validator' => $alert && $alert->vName ? ['name' => $alert->vName, 'id' => $alert->vId, 'at' => $alert->updated_at] : null,
            'status' => $alert ? (trim((string) $alert->auditorStatus) ?: 'pending') : null,
            'alert' => $alert ? [
                'region' => $alert->region,
                'province' => $alert->province,
                'detected' => $alert->detectionDate,
                'alertStatus' => $alert->alertStatus,
            ] : null,
            'history' => $history,
            'found' => (bool) ($auditor || $alert),
        ];
    }

    public function clearFind(): void
    {
        $this->alertCode = '';
        $this->lookup = null;
    }

    public function render()
    {
        return view('livewire.alert-lookup-component');
    }
}
