<?php

namespace App\Livewire;

use App\Events\UpdateAuditor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class TableAnalisis extends Component
{
    use WithPagination;
    public $isReason = false;
    public $search = '';
    public $alertId, $alertStatus, $alertReason;
    public $dataField = 'alertId', $dataOrder = 'asc', $paginate = 50;
    public $yearAlert;

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


        event(new UpdateAuditor);
        DB::table('alerts')
        ->where('isActive', 1)
        ->where('alertId', $id)->update([
            'auditorStatus' => $newStatus,
            'auditorReason' => null,
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);
        $this->dispatch('fix-alert');
        $this->closeReason();
    }

    public function updatedSearch(){
        $this->resetPage();
    }


    public function mount(){
        $this->yearAlert = 'all';
    }

    #[On('filterYear')]
    public function updateData($year)
    {
        $this->yearAlert = $year;
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
