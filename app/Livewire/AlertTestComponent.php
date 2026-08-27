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

    // sortable headers -> table-qualified columns; orderBy allowlist only, never raw input
    private function sortColumns(): array
    {
        $t = config('alerts.test_table');

        return [
            'alertId' => "$t.alertId",
            'created_at' => "$t.created_at",
            'auditorStatus' => "$t.auditorStatus",
        ];
    }

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
        $dataDelete = DB::table(config('alerts.test_table'))->where('alertId', $alertId)->where('isActive', 1)->first();
        $this->alertDeleteId = $dataDelete->alertId;
        $this->deleter = true;
    }

    public function deleting($alertId)
    {
        DB::table(config('alerts.test_table'))
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
        $this->resetPage();
    }

    public function closeReason()
    {
        $this->selectStatus = session('selectStatus');
        $this->isAudit = false;
        $this->alertId = null;
        $this->observation = null;
        $this->analis = null;
        $this->dispatch('close-audit-modal');
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
        if ($this->manualValidation()) {
            DB::table(config('alerts.test_table'))
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
            // broadcast only after the write succeeded
            event(new UpdateAnalis);
            Toaster::success('Success auditing Alert');
            $this->dispatch('close-audit-modal');
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
        $t = config('alerts.test_table');
        $data = DB::table($t)
        ->join('users', "$t.analisId", '=', 'users.id')
        ->select("$t.*", 'users.*')
        ->where("$t.isActive", 1)
        ->where("$t.alertId", $id)->first();
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
        $t = config('alerts.test_table');
        try {
            $query = DB::table($t)
            ->select(
                "$t.id",
                "$t.alertId",
                "$t.detectionDate",
                "$t.region",
                "$t.province",
                "$t.auditorStatus",
                "$t.created_at",
                "$t.platformStatus"
            )
            ->join('users', 'users.id', '=', "$t.analisId")
            ->where("$t.isActive", 1)
            ->where('users.is_active', 1);

            if (!empty($this->searchId)) {
                $query->where("$t.alertId", $this->searchId);
            }

            if ($this->selectStatus !== 'all') {
                $query->where("$t.auditorStatus", $this->selectStatus);
            }

            if ($this->yearAlert !== 'all') {
                $query->whereYear("$t.detectionDate", $this->yearAlert);
            }

            // column comes from the allowlist above only — never raw input
            $field = is_string($this->dataField) ? $this->dataField : '';
            $column = $this->sortColumns()[$field] ?? "$t.alertId";
            $order = is_string($this->dataOrder) && strtolower($this->dataOrder) === 'desc' ? 'desc' : 'asc';

            return $query->orderBy($column, $order)->paginate($this->paginate);

        } catch (\Throwable $th) {
            // The view calls paginator methods, so fail into an empty paginator, not an array.
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->paginate);
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
