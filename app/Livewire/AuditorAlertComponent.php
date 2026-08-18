<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AuditorAlertComponent extends Component
{
    use WithPagination;

    public $auditorId;
    public $yearAlert, $monthAlert, $selectStatus, $selectAction = 'all';
    public $searchId, $paginate = 30;

    /** Blank auditorStatus is a pending alert, not a status of its own. */
    private const STATUS_EXPR = "LOWER(COALESCE(NULLIF(a.auditorStatus, ''), 'pending'))";

    public function mount($id)
    {
        $this->auditorId = $id;
        session()->has('yearAlert') ? $this->yearAlert = session('yearAlert') : $this->yearAlert = 'all';
        session()->has('monthAlert') ? $this->monthAlert = session('monthAlert') : $this->monthAlert = 'all';
        session()->has('selectStatus') ? $this->selectStatus = session('selectStatus') : $this->selectStatus = 'all';
    }

    public function updatedYearAlert($value){
        session(['yearAlert' => $value]);
        $this->resetPage();
    }
    public function updatedMonthAlert($value){
        session(['monthAlert' => $value]);
        $this->resetPage();
    }
    public function updatedSelectStatus($value){
        session(['selectStatus' => $value]);
        $this->resetPage();
    }
    public function updatedSelectAction(){
        $this->resetPage();
    }
    public function updatedSearchId(){
        $this->resetPage();
    }

    public function filterByStatus($status){
        $this->selectStatus = $this->selectStatus === $status ? 'all' : $status;
        session(['selectStatus' => $this->selectStatus]);
        $this->resetPage();
    }

    public function resetScope(){
        $this->yearAlert = 'all';
        $this->monthAlert = 'all';
        $this->selectStatus = 'all';
        $this->selectAction = 'all';
        session(['yearAlert' => 'all', 'monthAlert' => 'all', 'selectStatus' => 'all']);
        $this->resetPage();
    }

    public function getAuditorName($id){
        return DB::table('users')->where('id', $id)->first();
    }

    private function hasSearch()
    {
        return $this->searchId !== null && $this->searchId !== '';
    }

    /**
     * Every panel reads the same scope: this auditor's log entries on active
     * alerts, filtered on the action date. Year/month/status persist in the
     * session like the validator pages do; the action filter is page-local.
     */
    private function scoped()
    {
        return DB::table('auditorlog as l')
            ->join('alerts as a', 'a.alertId', '=', 'l.alertId')
            ->where('l.auditorId', $this->auditorId)
            ->where('a.isActive', 1)
            ->when($this->yearAlert !== 'all', fn ($q) => $q->whereYear('l.created_at', $this->yearAlert))
            ->when($this->monthAlert !== 'all', fn ($q) => $q->whereMonth('l.created_at', $this->monthAlert))
            ->when($this->selectAction !== 'all', fn ($q) => $q->where('l.ngapain', $this->selectAction))
            ->when($this->selectStatus !== 'all', fn ($q) => $q->whereRaw(self::STATUS_EXPR . ' = ?', [$this->selectStatus]))
            ->when($this->hasSearch(), fn ($q) => $q->where('l.alertId', 'like', '%' . $this->searchId . '%'));
    }

    public function getKeyFigures()
    {
        return $this->scoped()->selectRaw("
                COUNT(*) AS actions,
                COUNT(DISTINCT l.alertId) AS alerts,
                SUM(CASE WHEN l.ngapain = 'auditing' THEN 1 ELSE 0 END) AS audits,
                SUM(CASE WHEN l.ngapain IN ('reexportimage', 'reclassification') THEN 1 ELSE 0 END) AS rework
            ")->first();
    }

    /** Current status of the distinct alerts this auditor has touched, in scope. */
    public function getStatusStats()
    {
        return $this->scoped()
            ->selectRaw(self::STATUS_EXPR . " AS status, COUNT(DISTINCT l.alertId) AS total")
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    public function getChartStats()
    {
        if ($this->yearAlert !== 'all') {
            $rows = $this->scoped()->selectRaw("MONTH(l.created_at) AS k, COUNT(*) AS c")
                ->groupBy('k')->orderBy('k')->pluck('c', 'k');

            return [
                'unit' => 'month',
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'keys' => range(1, 12),
                'values' => collect(range(1, 12))->map(fn ($m) => (int) ($rows[$m] ?? 0))->all(),
            ];
        }

        $rows = $this->scoped()->selectRaw("YEAR(l.created_at) AS k, COUNT(*) AS c")
            ->groupBy('k')->orderBy('k')->pluck('c', 'k');

        return [
            'unit' => 'year',
            'labels' => $rows->keys()->map(fn ($y) => (string) $y)->all(),
            'keys' => $rows->keys()->map(fn ($y) => (string) $y)->all(),
            'values' => $rows->values()->map(fn ($c) => (int) $c)->all(),
        ];
    }

    /**
     * Status x validator matrix: whose work this auditor reviews, and what
     * state those alerts are in now. Counts DISTINCT alerts so an alert
     * audited twice still weighs once.
     */
    public function getMatrix()
    {
        $rows = $this->scoped()
            ->join('users as v', 'v.id', '=', 'a.analisId')
            ->where('v.is_active', 1)
            ->selectRaw("v.name AS validator, " . self::STATUS_EXPR . " AS status, COUNT(DISTINCT l.alertId) AS total")
            ->groupBy('validator', 'status')
            ->get();

        $totals = [];
        $cells = [];
        foreach ($rows as $r) {
            $totals[$r->validator] = ($totals[$r->validator] ?? 0) + (int) $r->total;
            $cells[$r->status][$r->validator] = (int) $r->total;
        }
        arsort($totals);

        return ['validators' => array_keys($totals), 'totals' => $totals, 'cells' => $cells];
    }

    /**
     * The auditor's own work: one row per action, one column per period.
     * Only year/month/search apply - the action/status filters would hide
     * the very mix this panel is meant to show.
     */
    public function getActivityMatrix()
    {
        $byMonth = $this->yearAlert !== 'all';
        $periodExpr = $byMonth ? 'MONTH(l.created_at)' : 'YEAR(l.created_at)';

        $rows = DB::table('auditorlog as l')
            ->join('alerts as a', 'a.alertId', '=', 'l.alertId')
            ->where('l.auditorId', $this->auditorId)
            ->where('a.isActive', 1)
            ->when($this->yearAlert !== 'all', fn ($q) => $q->whereYear('l.created_at', $this->yearAlert))
            ->when($this->monthAlert !== 'all', fn ($q) => $q->whereMonth('l.created_at', $this->monthAlert))
            ->when($this->hasSearch(), fn ($q) => $q->where('l.alertId', 'like', '%' . $this->searchId . '%'))
            ->selectRaw("{$periodExpr} AS period, l.ngapain AS action, COUNT(DISTINCT l.alertId) AS total")
            ->groupBy('period', 'action')
            ->get();

        $labelFor = [
            'auditing' => 'Auditing',
            'Insert' => 'Insert',
            'refined' => 'Refined',
            'reclassification' => 'Re-classification',
            'reexportimage' => 'Re-export image',
            'Reject' => 'Reject',
            'deleting' => 'Deleting',
        ];

        $periods = $rows->pluck('period')->unique()->sort()->values();
        $labels = $byMonth
            ? $periods->mapWithKeys(fn ($m) => [$m => Carbon::create()->month((int) $m)->format('M')])
            : $periods->mapWithKeys(fn ($y) => [$y => (string) $y]);

        $actions = [];
        foreach ($rows as $r) {
            $a = &$actions[$r->action];
            $a['label'] ??= $labelFor[$r->action] ?? ucfirst($r->action);
            $a['periods'][$r->period] = ($a['periods'][$r->period] ?? 0) + (int) $r->total;
            $a['total'] = ($a['total'] ?? 0) + (int) $r->total;
            unset($a);
        }
        uasort($actions, fn ($x, $y) => $y['total'] <=> $x['total']);

        return [
            'periods' => $periods->all(),
            'labels' => $labels->all(),
            'actions' => $actions,
            'columnTotals' => $periods->mapWithKeys(fn ($p) => [
                $p => collect($actions)->sum(fn ($a) => $a['periods'][$p] ?? 0),
            ])->all(),
            'grandTotal' => collect($actions)->sum('total'),
        ];
    }

    public function getLogs()
    {
        try {
            return $this->scoped()
                ->join('users as v', 'v.id', '=', 'a.analisId')
                ->select(
                    'l.alertId',
                    'l.ngapain',
                    'l.created_at as auditedAt',
                    'a.region',
                    'a.province',
                    'a.auditorStatus',
                    'v.name as validator'
                )
                ->orderByDesc('l.created_at')
                ->paginate($this->paginate);
        } catch (\Throwable $th) {
            // The view calls paginator methods, so fail into an empty paginator, not an array.
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->paginate);
        }
    }

    public function render()
    {
        $logs = $this->getLogs();
        $auditorName = $this->getAuditorName($this->auditorId);
        $figures = $this->getKeyFigures();
        $statusStats = $this->getStatusStats();
        $chart = $this->getChartStats();
        $matrix = $this->getMatrix();
        $activity = $this->getActivityMatrix();
        return view('livewire.auditor-alert-component', compact('logs', 'auditorName', 'figures', 'statusStats', 'chart', 'matrix', 'activity'));
    }
}
