<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CheckAlertAnalis extends Component
{
    use WithPagination;
    public $searchName;
    public $dataField = 'name', $dataOrder = 'asc';

    public $yearAlert;

    public function mount(){
        $this->yearAlert = Carbon::now()->format('Y');
    }

    #[On('filterYear')]
    public function updateData($year)
    {
        $this->yearAlert = $year;
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
    public function getAlerts(){
        $sc = '%' . $this->searchName . '%';
        $query = DB::table('alerts')
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
                SUM(CASE WHEN alerts.platformStatus = 'sccon' THEN 1 ELSE 0 END) AS sccon,
                SUM(CASE WHEN alerts.platformStatus = 'workspace' THEN 1 ELSE 0 END) AS workspace,
                COUNT(alerts.alertId) AS total
            ")
            ->when(!empty($sc), function ($q) use ($sc) {
                return $q->where('users.name', 'like', $sc);
            })
            ->when($this->yearAlert !== 'all', function ($q) {
                return $q->whereYear('alerts.detectionDate', $this->yearAlert);
            })
            ->where('alerts.isActive', 1)     // alerts active flag
            ->where('users.is_active', 1)     // user active flag
            ->groupBy('alerts.analisId', 'users.name')
            ->orderBy($this->dataField, $this->dataOrder)
            ->get();

        return $query;

    }
    public function render()
    {
        $alerts = $this->getAlerts();
        // dd($alerts);
        return view('livewire.check-alert-analis', compact('alerts'));
    }
}
