<?php

namespace App\Livewire;

use App\Events\UpdateAuditor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Lazy]
class TableAnalisis extends Component
{
    use WithPagination;
    public $isReason = false;
    public $search = '';
    public $alertId, $alertStatus, $alertReason;
    public $dataField = 'alertId', $dataOrder = 'asc', $paginate = 50;
    public $yearAlert;
    public $monthAlert;

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

    public function sortingField($field){
        $this->dataField = $field;
        $this->dataOrder = $this->dataOrder == 'asc' ? 'desc' : 'asc';
    }



    public function closeReason(){
        $this->isReason = false;
        $this->alertId = null;
        $this->alertStatus = null;
        $this->alertReason = null;
    }

    public function showReason($id){
        $this->isReason = true;
        //load data to delete function
        $data = DB::table('alerts')->where('id', $id)->where('isActive', 1)->first();
        $this->alertId = $data->alertId;
        $this->alertStatus = $data->auditorStatus;
        $this->alertReason = $data->auditorReason;
    }


    public function fixAlert($id){
        // dd($this->alertStatus);
        if ($this->alertStatus === 'reexportimage') {
            $newStatus = 'pre-approved';
            DB::table('auditorlog')->insert([
                'auditorId' => session('id'),
                'alertId' => $id,
                'ngapain' => 'reexportimage',
                'created_at' => Carbon::now('Asia/Jakarta')
            ]);
        } elseif ($this->alertStatus === 'reclassification') {
            $newStatus = 'refined';
            DB::table('auditorlog')->insert([
                'auditorId' => session('id'),
                'alertId' => $id,
                'ngapain' => 'reclassification',
                'created_at' => Carbon::now('Asia/Jakarta')
            ]);
        } else {
            $newStatus = 'pending';
        }


        DB::table('alerts')
        ->where('isActive', 1)
        ->where('alertId', $id)->update([
            'auditorStatus' => $newStatus,
            'auditorReason' => null,
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);
        // broadcast only after the write succeeded
        event(new UpdateAuditor);
        $this->dispatch('fix-alert');
        $this->closeReason();
    }

    public function updatedSearch(){
        $this->resetPage();
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

    #[On('echo:analis-data,UpdateAnalis')]
    public function getAlerts(){
        $sc = '%' . $this->search . '%';
        try {
           return DB::table('alerts')
            ->join('users', 'users.id', '=', 'alerts.analisId')
            ->select(
                'alerts.id',
                'alerts.alertId',
                'alerts.alertStatus',
                'alerts.detectionDate',
                'alerts.region',
                'alerts.province',
                'alerts.auditorStatus',
                'alerts.created_at'
            )
            ->whereNotNull('alerts.auditorStatus')
            ->whereNotIn('alerts.auditorStatus', [
                'approved', 'rejected', 'duplicate', 'pre-approved', 'refined', 'error'
            ])
            ->when($this->yearAlert !== 'all', function ($q) {
                return $q->whereYear('alerts.detectionDate', $this->yearAlert);
            })
            ->when($this->monthAlert !== 'all', function ($q) {
                return $q->whereMonth('alerts.detectionDate', $this->monthAlert);
            })
            ->when(!empty($sc), function ($q) use ($sc) {
                return $q->where('alerts.alertId', 'like', $sc);
            })
            ->where('alerts.isActive', 1)
            ->where('users.is_active', 1) // only include alerts whose user is active
            ->orderBy($this->dataField, $this->dataOrder)
            ->paginate($this->paginate);

        } catch (\Throwable $th) {
            return [];
        }
    }
    public function render()
    {
        $databases = $this->getAlerts();
        return view('livewire.table-analisis', compact('databases'));
    }
}
