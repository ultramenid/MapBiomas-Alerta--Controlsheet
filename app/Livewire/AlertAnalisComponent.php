<?php

namespace App\Livewire;

use App\Events\UpdateAnalis;
use App\Events\UpdateAuditor;
use App\Exports\ValidatorExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Masmerise\Toaster\Toaster;

class AlertAnalisComponent extends Component
{
    public $analisId;

    use WithPagination;
    public $isAudit = false;
    public $alertId, $alertStatus, $alertReason, $analis, $alertNote, $observation, $statusAlert;
    public $dataField = 'alertId', $dataOrder = 'asc', $paginate = 30, $searchId;
    public $deleter = false, $alertDeleteId, $selectStatus, $yearAlert;


     // https://laravel-excel.com/
    public function exportExcel(){
        return  Excel::download(new ValidatorExport($this->selectStatus, $this->yearAlert, $this->analisId), 'ValidatorExport.xlsx');
    }

    public function getAnalisName($id){
        return DB::table('users')->where('id', $id)->first();
    }
    public function closeDelete(){
        $this->deleter = false;
        $this->alertDeleteId = null;
    }
    public function deleteAlert($alertId){
        //load data to delete function
        $dataDelete = DB::table('alerts')->where('alertId', $alertId)->where('isActive', 1)->first();
        $this->alertDeleteId = $dataDelete->alertId;
        $this->deleter = true;
    }
    public function deleting($alertId){
        DB::table('alerts')
        ->where('alertId', $alertId)
        ->where('isActive', 1)
        ->delete();

        DB::table('auditorlog')->insert([
                'auditorId' => session('id'),
                'alertId' => $alertId,
                'ngapain' => 'deleting',
                'created_at' => Carbon::now('Asia/Jakarta')
        ]);
        $this->dispatch('alert-deleted');
        Toaster::success('Success deleting Alert');
        $this->closeDelete();
        event(new UpdateAnalis);
        event(new UpdateAuditor);
    }

    public function mount($id)
    {
        $this->analisId = $id;
        session()->has('selectStatus') ? $this->selectStatus = session('selectStatus') : $this->selectStatus = 'all';
        $this->yearAlert = session('yearAlert');
    }
    public function updatedYearAlert($value){
        session(['yearAlert' => $value]);
        $this->resetPage();
    }
    public function sortingField($field){
        $this->dataField = $field;
        $this->dataOrder = $this->dataOrder == 'asc' ? 'desc' : 'asc';
    }
    public function updatedSearchId(){
        $this->resetPage();
    }

    public function updatedSelectStatus($value){
        session(['selectStatus' => $value]);
        // dd(session()/->all());
        $this->resetPage();
    }

    #[On('echo:analis-data,UpdateAnalis')]
    #[On('echo:auditor-data,UpdateAuditor')]
    public function getAlerts(){
        $sc = '%' . $this->searchId . '%';
        try {
            $query = DB::table('alerts')
                ->join('users', 'users.id', '=', 'alerts.analisId')
                ->select(
                    'alerts.id',
                    'alerts.alertId',
                    'alerts.detectionDate',
                    'alerts.region',
                    'alerts.province',
                    'alerts.auditorStatus',
                    'alerts.created_at',
                    'alerts.platformStatus'
                )
                ->where('alerts.analisId', $this->analisId)
                ->where('alerts.isActive', 1)
                ->where('users.is_active', 1);

            if (!empty($sc)) {
                $query->where('alerts.alertId', 'like', $sc);
            }

            if ($this->selectStatus !== 'all') {
                $query->where('alerts.auditorStatus', $this->selectStatus);
            }

            if ($this->yearAlert !== 'all') {
                $query->whereYear('alerts.detectionDate', $this->yearAlert);
            }

            return $query->paginate($this->paginate);
        } catch (\Throwable $th) {
            return [];
        }
    }

    public function closeReason(){
        $this->selectStatus = session('selectStatus');
        $this->isAudit = false;
        // dd(session()->all());
    }

    public function checkAlertStatus(){

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

    public function auditing($alertId){
        if(!$this->manualValidation()){
            return;
        }

        DB::table('alerts')
        ->where('isActive', 1)
        ->where('alertId', $alertId)
        ->update([
            'alertStatus' => $this->checkAlertStatus(),
            'auditorStatus' => $this->alertStatus,
            'auditorReason' => $this->alertReason,
            'updated_at' => Carbon::now('Asia/Jakarta')
        ]);
        DB::table('auditorlog')->insert([
            'auditorId' => session('id'),
            'alertId' => $alertId,
            'ngapain' => 'auditing',
            'created_at' => Carbon::now('Asia/Jakarta')
        ]);
        event(new UpdateAnalis);
        redirect()->to(url()->previous());
    }
    public function showAudit($id){
        $this->isAudit = true;
        //load data to delete function
        $data = DB::table('alerts')
        ->join('users', 'analisId', '=', 'users.id')
        ->select('alerts.*', 'users.*')
        ->where('alerts.isActive', 1)
        ->where('alertId', $id)->first();
        $this->alertId = $data->alertId;
        $this->analis = $data->name;
        $this->observation = $data->observation;
        $this->alertNote = $data->alertNote;
        $this->statusAlert = $data->alertStatus;
        $this->alertStatus = $data->auditorStatus;

    }

    public function render()
    {
        // dd($this->getAlerts());
        $databases = $this->getAlerts();
        $analisName = $this->getAnalisName($this->analisId);
        return view('livewire.alert-analis-component', compact('databases', 'analisName'));
    }

    public function manualValidation(){
        if($this->alertStatus == ''){
            Toaster::error('Alert status is required!');
            return;
        }elseif($this->alertReason == '' and $this->alertStatus != 'approved'){
            Toaster::error('Alert reason is required!');
            return;
        }
        return true;
    }
}
