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
    // whole-log daily series, no filter state — a single shared cache key fits
    private const CACHE_KEY = 'dashboard:work-trend:v1';

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
        $this->forgetCached(self::CACHE_KEY);
        $this->loadTrend();
    }

    public function loadTrend(): void
    {
        // admin-only card — never even query for other roles
        if (! $this->isAdmin()) {
            $this->trend = ['dates' => [], 'auditor' => [], 'validator' => []];
            return;
        }

        // one scan of auditorlog: 'auditing' rows are auditor work, the other
        // five actions are validator work (same split as ValidatorTaskComponent)
        $rows = $this->cached(self::CACHE_KEY, 60, function () {
            return DB::table('auditorlog')
                ->join('users', 'users.id', '=', 'auditorlog.auditorId')
                ->where('users.is_active', 1)
                ->whereIn('auditorlog.ngapain', ['auditing', 'Insert', 'Reject', 'reclassification', 'reexportimage', 'refined'])
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

    private function isAdmin(): bool
    {
        // strict: null == 0 is true in PHP, so an unauthenticated Livewire
        // render must not slip through a loose role comparison
        return session('role_id') !== null && (int) session('role_id') === 0;
    }

    public function render()
    {
        $isAdmin = $this->isAdmin();
        $payload = $isAdmin
            ? $this->trend
            : ['dates' => [], 'auditor' => [], 'validator' => []];

        return view('livewire.work-trend-chart-component', [
            'isAdmin' => $isAdmin,
            'payload' => $payload,
            // changing key makes Livewire swap the node, which re-runs the
            // chart bootstrap in x-init with the fresh data-payload
            'payloadKey' => md5(json_encode($payload)),
        ]);
    }
}
