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

class AuditorDatabaseComponent extends Component
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

    // ponytail: 'all' = everyone's alerts, 'mine' = scoped to session id
    public $selectOwner = 'all';

    // sortable headers -> table-qualified columns; orderBy allowlist only, never raw input
    private array $sortColumns = [
        'alertId' => 'alerts.alertId',
        'detectionDate' => 'alerts.detectionDate',
        'created_at' => 'alerts.created_at',
        'auditorStatus' => 'alerts.auditorStatus',
    ];

    public function mount()
    {
        //cek if session selectstatus exist if not set to 'all'
        session()->has('selectStatus') ? $this->selectStatus = session('selectStatus') : $this->selectStatus = 'all';
        $this->yearAlert = session('yearAlert', 'all');
        $this->selectOwner = session('selectOwner', 'all');
    }

    // auditorStatus is nullable and blank means the same as 'pending' (the insert default)
    private const STATUS_SQL = "LOWER(COALESCE(NULLIF(alerts.auditorStatus, ''), 'pending'))";

    public function filterStatus($status)
    {
        $this->selectStatus = $this->selectStatus === $status ? 'all' : $status;
        $this->updatedSelectStatus($this->selectStatus);
    }

    public function resetScope()
    {
        $this->searchId = '';
        $this->selectStatus = 'all';
        $this->yearAlert = 'all';
        $this->selectOwner = 'all';
        session(['selectStatus' => 'all', 'yearAlert' => 'all', 'selectOwner' => 'all']);
        $this->resetPage();
    }

    public function updatedYearAlert($value)
    {
        session(['yearAlert' => $value]);
        $this->resetPage();
    }

    public function updatedSelectOwner($value)
    {
        session(['selectOwner' => $value]);
        $this->resetPage();
    }

    public function closeDelete()
    {
        $this->deleter = false;
        $this->alertDeleteId = null;
    }

    public function deleteAlert($alertId)
    {
        if (! $this->canDelete($alertId)) {
            abort(403);
        }

        // load data to delete function
        $dataDelete = DB::table('alerts')->where('alertId', $alertId)->where('isActive', 1)->first();
        $this->alertDeleteId = $dataDelete->alertId;
        $this->deleter = true;
    }

    // ponytail: terminal states (approved/rejected/duplicate) are locked for everyone; else admin (0) any, analis (2) own, role 1 none
    protected function canDelete($alertId)
    {
        $alert = DB::table('alerts')
            ->where('alertId', $alertId)
            ->where('isActive', 1)
            ->first(['analisId', 'auditorStatus']);

        if (! $alert || in_array($alert->auditorStatus, ['approved', 'rejected', 'duplicate'])) {
            return false;
        }

        $role = session('role_id');

        if ($role == 0) {
            return true;
        }

        if ($role == 2) {
            return $alert->analisId == session('id');
        }

        return false;
    }

    public function deleting($alertId)
    {
        if (! $this->canDelete($alertId)) {
            abort(403);
        }

        DB::table('alerts')
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
        // same column flips direction; a new column starts ascending
        $this->dataOrder = $this->dataField === $field && $this->dataOrder === 'asc' ? 'desc' : 'asc';
        $this->dataField = $field;
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
        if (session('role_id') == 2) {
            abort(403);
        }

        // dd($this->alertStatus);
        if ($this->manualValidation()) {
            DB::table('alerts')
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

    // ponytail: inline status dropdown — only the validator (role 2) switches working states; auditor/admin use the audit dialog
    #[On('updateStatus')]
    public function updateStatus($id, $status)
    {
        if (session('role_id') != 2) {
            abort(403);
        }

        if ($status == 'refined') {
            DB::table('auditorlog')->insert([
                'auditorId' => session('id'),
                'alertId' => $id,
                'ngapain' => 'refined',
                'created_at' => Carbon::now('Asia/Jakarta'),
            ]);
        }

        DB::table('alerts')
            ->where('alertId', $id)
            ->where('isActive', 1)
            ->update([
                'auditorStatus' => $status,
                'updated_at' => Carbon::now('Asia/Jakarta'),
            ]);

        event(new UpdateAnalis);
        Toaster::success('Successfully change platform status');
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
        $data = DB::table('alerts')
        ->join('users', 'analisId', '=', 'users.id')
        ->select('alerts.*', 'users.*')
        ->where('alerts.isActive', 1)
        ->where('alertId', $id)->first();
        // dd($data);
        $this->alertId = $data->alertId;
        $this->analis = $data->name;
        $this->observation = $data->observation;
        $this->alertNote = $data->alertNote;
        $this->statusAlert = $data->alertStatus;
        $this->alertStatus = $data->auditorStatus;

    }

    // everything except the status filter, so the status chips count within the same scope
    protected function scopedQuery()
    {
        $query = DB::table('alerts')
            ->join('users', 'users.id', '=', 'alerts.analisId')
            ->where('alerts.isActive', 1)
            ->where('users.is_active', 1);

        if (filled($this->searchId)) {
            $query->where('alerts.alertId', $this->searchId);
        }

        if ($this->selectOwner === 'mine') {
            $query->where('alerts.analisId', session('id'));
        }

        if ($this->yearAlert && $this->yearAlert !== 'all') {
            $query->whereYear('alerts.detectionDate', $this->yearAlert);
        }

        return $query;
    }

    #[On('echo:analis-data,UpdateAnalis')]
    #[On('echo:auditor-data,UpdateAuditor')]
    public function getAlerts()
    {
        try {
            $query = $this->scopedQuery()->select(
                'alerts.id',
                'alerts.alertId',
                'alerts.detectionDate',
                'alerts.region',
                'alerts.province',
                'alerts.auditorStatus',
                'alerts.created_at',
                'alerts.analisId',
                'users.name as validator'
            );

            if ($this->selectStatus === 'pending') {
                $query->whereRaw(self::STATUS_SQL." = 'pending'");
            } elseif ($this->selectStatus !== 'all') {
                $query->where('alerts.auditorStatus', $this->selectStatus);
            }

            // column comes from the allowlist above only — never raw input
            $field = is_string($this->dataField) ? $this->dataField : '';
            $column = $this->sortColumns[$field] ?? 'alerts.alertId';
            $order = is_string($this->dataOrder) && strtolower($this->dataOrder) === 'desc' ? 'desc' : 'asc';

            return $query->orderBy($column, $order)->paginate($this->paginate);

        } catch (\Throwable $th) {
            // The view calls paginator methods, so fail into an empty paginator, not an array.
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->paginate);
        }
    }

    public function render()
    {
        $databases = $this->getAlerts();

        $statusStats = $this->scopedQuery()
            ->selectRaw(self::STATUS_SQL.' as status, COUNT(*) as total')
            ->groupByRaw(self::STATUS_SQL)
            ->pluck('total', 'status');

        return view('livewire.auditor-database-component', compact('databases', 'statusStats'));
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
}
