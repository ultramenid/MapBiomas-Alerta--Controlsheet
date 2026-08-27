<?php

namespace App\Livewire;

use App\Livewire\Concerns\CachesAggregates;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class WorkTrendChartComponent extends Component
{
    use CachesAggregates;
    // admin: one shared key for the whole-log series; role 1/2: user-scoped key
    private const CACHE_KEY_ADMIN = 'dashboard:work-trend:v1';
    private const CACHE_KEY_USER = 'dashboard:work-trend:user:v1';

    // Daily totals over the whole log history. The client-side brush in the
    // card footer does the time-range selection, so there is no date state here.
    public array $trend = ['dates' => [], 'auditor' => [], 'validator' => []];

    public function mount(): void
    {
        $this->loadTrend();
    }

    #[On('echo:analis-data,UpdateAnalis')]
    #[On('echo:auditor-data,UpdateAuditor')]
    public function refreshTrend(): void
    {
        // realtime events must show through the 60s cache window
        $role = session('role_id');
        $key = ((int) $role === 0) ? self::CACHE_KEY_ADMIN : self::CACHE_KEY_USER . ':' . session('id');
        $this->forgetCached($key);
        $this->loadTrend();
    }

    public function loadTrend(): void
    {
        $role = session('role_id');

        if ($role === null || ! in_array((int) $role, [0, 1, 2], true)) {
            $this->trend = ['dates' => [], 'auditor' => [], 'validator' => []];
            return;
        }

        $isAdmin = (int) $role === 0;
        $cacheKey = $isAdmin ? self::CACHE_KEY_ADMIN : self::CACHE_KEY_USER . ':' . session('id');
        $userId = $isAdmin ? null : session('id');

        $rows = $this->cached($cacheKey, 60, function () use ($isAdmin, $userId) {
            $query = DB::table('auditorlog')
                ->join('users', 'users.id', '=', 'auditorlog.auditorId')
                ->where('users.is_active', 1)
                ->whereIn('auditorlog.ngapain', ['auditing', 'Insert', 'Reject', 'reclassification', 'reexportimage', 'refined']);

            // role 1/2: only their own work
            if (! $isAdmin && $userId) {
                $query->where('auditorlog.auditorId', $userId);
            }

            return $query
                ->select(
                    DB::raw('DATE(auditorlog.created_at) as d'),
                    DB::raw("COUNT(DISTINCT CASE WHEN auditorlog.ngapain = 'auditing' THEN auditorlog.alertId END) as auditor"),
                    DB::raw("COUNT(DISTINCT CASE WHEN auditorlog.ngapain IN ('Insert', 'Reject', 'reclassification', 'reexportimage', 'refined') THEN auditorlog.alertId END) as validator")
                )
                ->groupBy(DB::raw('DATE(auditorlog.created_at)'))
                ->orderBy('d')
                ->get();
        });

        if ($rows->isEmpty()) {
            $this->trend = ['dates' => [], 'auditor' => [], 'validator' => []];
            return;
        }

        $auditor = [];
        $validator = [];
        foreach ($rows as $r) {
            $auditor[$r->d] = (int) $r->auditor;
            $validator[$r->d] = (int) $r->validator;
        }

        // fill a continuous daily series so gaps read as zero-activity days
        $dates = [];
        $a = [];
        $v = [];
        foreach (CarbonPeriod::create($rows[0]->d, Carbon::now('Asia/Jakarta')->format('Y-m-d')) as $dt) {
            $ymd = $dt->format('Y-m-d');
            $dates[] = $ymd;
            $a[] = $auditor[$ymd] ?? 0;
            $v[] = $validator[$ymd] ?? 0;
        }

        $this->trend = ['dates' => $dates, 'auditor' => $a, 'validator' => $v];
    }

    public function render()
    {
        $role = session('role_id') === null ? null : (int) session('role_id');

        return view('livewire.work-trend-chart-component', [
            'role' => $role,
            'payload' => $this->trend,
            'payloadKey' => md5(json_encode($this->trend)),
        ]);
    }
}
