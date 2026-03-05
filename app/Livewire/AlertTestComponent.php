<?php

namespace App\Livewire;

use App\Events\UpdateAnalis;
use App\Events\UpdateAuditor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class AlertTestComponent extends Component
{
    use WithPagination;

    public $isAudit = false;

    public $alertId;

    public $alertStatus, $statusAlert;

    public $alertReason;

    public $analis;

    public $alertNote;

    public $observation;

    public $dataField = 'alertId';

    public $dataOrder = 'asc';

    public $paginate = 30;

    public $searchId;

    public $deleter = false;

    public $alertDeleteId;

    public $selectStatus;

    public $yearAlert;

    public function mount()
    {
        //cek if session selectstatus exist if not set to 'all'
        session()->has('selectStatus') ? $this->selectStatus = session('selectStatus') : $this->selectStatus = 'all';
        $this->yearAlert = session('yearAlert');
    }

    public function updatedYearAlert($value)
    {
        session(['yearAlert' => $value]);
        $this->resetPage();
    }

    public function closeDelete()
    {
        $this->deleter = false;
        $this->alertDeleteId = null;
    }

    public function deleteAlert($alertId)
    {

        // load data to delete function
        $dataDelete = DB::table('alerts_backup_terbaru')->where('alertId', $alertId)->where('isActive', 1)->first();
        $this->alertDeleteId = $dataDelete->alertId;
        $this->deleter = true;
    }

    public function deleting($alertId)
    {
        DB::table('alerts_backup_terbaru')
            ->where('alertId', $alertId)
            ->where('isActive', 1)
            ->delete();

        DB::table('auditorlog')->insert([
            'auditorId' => session('id'),
            'alertId' => $alertId,
            'ngapain' => 'deleting',
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);
        Toaster::success('Success deleting Alert');
        $this->closeDelete();
        event(new UpdateAnalis);
        event(new UpdateAuditor);

    }

    public function sortingField($field)
    {
        $this->dataField = $field;
        $this->dataOrder = $this->dataOrder == 'asc' ? 'desc' : 'asc';
    }

    public function closeReason()
    {
        $this->selectStatus = session('selectStatus');
       redirect()->to(url()->previous());
        // dd(session()->all());
    }

    public function checkAlertStatus(){

        // dd($this->alertStatus);
        $status = $this->alertStatus;
        if($status == 'rejected'){
            $status = 'rejected';
        }elseif($status == 'duplicate'){
            $status = 'duplicate';
        }else{
            $status = $this->statusAlert;
        }
        return $status;
    }


    public function auditing($alertId)
    {
        // dd($this->alertStatus);
        event(new UpdateAnalis);
        if ($this->manualValidation()) {
            DB::table('alerts_backup_terbaru')
                ->where('isActive', 1)
                ->where('alertId', $alertId)
                ->update([
                    'alertStatus' => $this->checkAlertStatus(),
                    'auditorStatus' => $this->alertStatus,
                    'auditorReason' => $this->alertReason,
                    'updated_at' => Carbon::now('Asia/Jakarta'),
                ]);
            DB::table('auditorlog')->insert([
                'auditorId' => session('id'),
                'alertId' => $alertId,
                'ngapain' => 'auditing',
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);
            // Jangan redirect, biarkan AlpineJS yang close modal
        }

    }

    public function updatedSearchId()
    {
        $this->resetPage();
    }

    public function updatedSelectStatus($value)
    {
        session(['selectStatus' => $value]);
        $this->resetPage();
    }

    public function showAudit($id)
    {
         $this->isAudit = true;
        $data = DB::table('alerts_backup_terbaru')
        ->join('users', 'alerts_backup_terbaru.analisId', '=', 'users.id')
        ->select('alerts_backup_terbaru.*', 'users.*')
        ->where('alerts_backup_terbaru.isActive', 1)
        ->where('alerts_backup_terbaru.alertId', $id)->first();
        // dd($data);
        $this->alertId = $data->alertId;
        $this->analis = $data->name;
        $this->observation = $data->observation;
        $this->alertNote = $data->alertNote;
        $this->statusAlert = $data->alertStatus;
        $this->alertStatus = $data->auditorStatus;

    }

    #[On('echo:analis-data,UpdateAnalis')]
    #[On('echo:auditor-data,UpdateAuditor')]
    public function getAlerts()
    {
        $sc = '%'.$this->searchId.'%';
        try {
            $query = DB::table('alerts_backup_terbaru')
            ->select(
                'alerts_backup_terbaru.id',
                'alerts_backup_terbaru.alertId',
                'alerts_backup_terbaru.detectionDate',
                'alerts_backup_terbaru.region',
                'alerts_backup_terbaru.province',
                'alerts_backup_terbaru.auditorStatus',
                'alerts_backup_terbaru.created_at',
                'alerts_backup_terbaru.platformStatus'
            )
            ->join('users', 'users.id', '=', 'alerts_backup_terbaru.analisId')
            ->where('alerts_backup_terbaru.isActive', 1)
            ->where('users.is_active', 1);

            if (!empty($this->searchId)) {
                $query->where('alerts_backup_terbaru.alertId', $this->searchId);
            }

            if ($this->selectStatus !== 'all') {
                $query->where('alerts_backup_terbaru.auditorStatus', $this->selectStatus);
            }

            if ($this->yearAlert !== 'all') {
                $query->whereYear('alerts_backup_terbaru.detectionDate', $this->yearAlert);
            }

            return $query->paginate($this->paginate);

        } catch (\Throwable $th) {
            return [];
        }
    }



    public function manualValidation()
    {
        if ($this->alertStatus == '') {
            Toaster::error('Alert status is required!');

            return;
        } elseif ($this->alertReason == '' and $this->alertStatus != 'approved') {
            Toaster::error('Alert reason is required!');

            return;
        }

        return true;
    }
    public function render()
    {
         $databases = $this->getAlerts();
        return view('livewire.alert-test-component', compact('databases'));
    }
}
