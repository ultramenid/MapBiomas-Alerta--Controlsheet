<?php

namespace App\Livewire;

use App\Livewire\Concerns\CachesAggregates;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Lazy]
class CheckAlertAnalis extends Component
{
    use CachesAggregates;
    use WithPagination;
    public $searchName;
    public $dataField = 'name', $dataOrder = 'asc';

    public $yearAlert;
    public $monthAlert;

    // sortable headers -> selected/grouped columns; orderBy allowlist only, never raw input
    private array $sortColumns = [
        'name' => 'users.name',
        'approved' => 'approved',
        'rejected' => 'rejected',
        'duplicate' => 'duplicate',
        'reexportimage' => 'reexportimage',
        'reclassification' => 'reclassification',
        'preapproved' => 'preapproved',
        'refined' => 'refined',
        'error' => 'error',
        'total' => 'total',
    ];

    public static function placeholder()
    {
        // subtle pulsing skeleton sized like the panel it replaces
        return <<<'HTML'
        <div class="glass rounded-sm p-5 mb-5 z-20 relative animate-pulse">
            <div class="h-9 w-52 rounded-sm bg-stone-200 dark:bg-slate-700 mb-6"></div>
            <div class="h-72 w-full rounded-sm bg-stone-100 dark:bg-slate-800"></div>
        </div>
        HTML;
    }

    private function cacheKey(){
        // row set depends on the search term, the year/month filter and the sort
        return 'dashboard:check-alert:v2:'.(string) $this->searchName.':'.$this->yearAlert.':'.$this->monthAlert.':'.$this->dataField.':'.$this->dataOrder;
    }

    public function mount(){
        $this->yearAlert = 'all';
        $this->monthAlert = 'all';
    }

    #[On('filterYear')]
    public function updateData($year, $month)
    {
        $this->yearAlert = $year;
        $this->monthAlert = $month;
    }

    public function sortingField($field){
        $this->dataField = $field;
        $this->dataOrder = $this->dataOrder == 'asc' ? 'desc' : 'asc';
    }

    public function updatedSearchName(){
        $this->resetPage();
    }

    #[On('echo:analis-data,UpdateAnalis')]
    #[On('echo:auditor-data,UpdateAuditor')]
    public function refreshAlerts(){
        // realtime events must show through the 60s cache window
        $this->forgetCached($this->cacheKey());
    }

    public function getAlerts(){
        $sc = '%' . $this->searchName . '%';
        // column comes from the allowlist above only — never raw input
        $field = is_string($this->dataField) ? $this->dataField : '';
        $sortColumn = $this->sortColumns[$field] ?? 'users.name';
        $order = is_string($this->dataOrder) && strtolower($this->dataOrder) === 'desc' ? 'desc' : 'asc';
        $query = $this->cached($this->cacheKey(), 60, function () use ($sc, $sortColumn, $order) {
            return DB::table('alerts')
            ->join('users', 'alerts.analisId', '=', 'users.id')
            ->selectRaw("
                users.name,
                users.id as userId,
                alerts.analisId,
                SUM(CASE WHEN alerts.auditorStatus = 'approved' THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN alerts.auditorStatus = 'rejected' THEN 1 ELSE 0 END) AS rejected,
                SUM(CASE WHEN alerts.auditorStatus = 'duplicate' THEN 1 ELSE 0 END) AS duplicate,
                SUM(CASE WHEN alerts.auditorStatus = 'reexportimage' THEN 1 ELSE 0 END) AS reexportimage,
                SUM(CASE WHEN alerts.auditorStatus = 'reclassification' THEN 1 ELSE 0 END) AS reclassification,
                SUM(CASE WHEN alerts.auditorStatus = 'pre-approved' THEN 1 ELSE 0 END) AS preapproved,
                SUM(CASE WHEN alerts.auditorStatus = 'refined' THEN 1 ELSE 0 END) AS refined,
                SUM(CASE WHEN alerts.auditorStatus = 'error' THEN 1 ELSE 0 END) AS error,
                COUNT(alerts.alertId) AS total
            ")
            ->when(!empty($sc), function ($q) use ($sc) {
                return $q->where('users.name', 'like', $sc);
            })
            ->when($this->yearAlert !== 'all', function ($q) {
                // detectionDate is a 'YYYY-MM-DD' string, so an explicit range
                // compares lexicographically and stays sargable
                $start = $this->yearAlert.'-01-01';
                $end = $this->yearAlert.'-12-31';
                if ($this->monthAlert !== 'all') {
                    $month = Carbon::createFromDate((int) $this->yearAlert, (int) $this->monthAlert, 1);
                    $start = $month->copy()->startOfMonth()->toDateString();
                    $end = $month->copy()->endOfMonth()->toDateString();
                }
                return $q->whereBetween('alerts.detectionDate', [$start, $end]);
            })
            ->when($this->yearAlert === 'all' && $this->monthAlert !== 'all', function ($q) {
                // month-only across every year cannot be a single sargable range
                return $q->whereMonth('alerts.detectionDate', $this->monthAlert);
            })
            ->where('alerts.isActive', 1)     // alerts active flag
            ->where('users.is_active', 1)     // user active flag
            ->groupBy('alerts.analisId', 'users.name')
            ->orderBy($sortColumn, $order)
            ->get();
        });

        return $query;

    }
    public function render()
    {
        $alerts = $this->getAlerts();
        // dd($alerts);
        return view('livewire.check-alert-analis', compact('alerts'));
    }
}
