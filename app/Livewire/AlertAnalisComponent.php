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
    public $deleter = false, $alertDeleteId, $selectStatus, $yearAlert, $monthAlert;


     // https://laravel-excel.com/
    public function exportExcel(){
        return  Excel::download(new ValidatorExport($this->selectStatus, $this->yearAlert, $this->monthAlert, $this->analisId), 'ValidatorExport.xlsx');
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
        session()->has('yearAlert') ? $this->yearAlert = session('yearAlert') : $this->yearAlert = 'all';
        session()->has('monthAlert') ? $this->monthAlert = session('monthAlert') : $this->monthAlert = 'all';
    }
    public function updatedYearAlert($value){
        session(['yearAlert' => $value]);
        $this->resetPage();
    }
    public function updatedMonthAlert($value){
        session(['monthAlert' => $value]);
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

    public function filterByStatus($status){
        $this->selectStatus = $this->selectStatus === $status ? 'all' : $status;
        session(['selectStatus' => $this->selectStatus]);
        $this->resetPage();
    }

    public function resetScope(){
        $this->yearAlert = 'all';
        $this->monthAlert = 'all';
        $this->selectStatus = 'all';
        session(['yearAlert' => 'all', 'monthAlert' => 'all', 'selectStatus' => 'all']);
        $this->resetPage();
    }

    #[On('echo:analis-data,UpdateAnalis')]
    #[On('echo:auditor-data,UpdateAuditor')]
    public function getAlerts(){
        try {
            $query = $this->scoped()
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
                ->where('users.is_active', 1);

            if ($this->selectStatus !== 'all') {
                $query->whereRaw(self::STATUS_EXPR . ' = ?', [$this->selectStatus]);
            }

            return $query->paginate($this->paginate);
        } catch (\Throwable $th) {
            // The view calls paginator methods, so fail into an empty paginator, not an array.
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->paginate);
        }
    }

    /** Every panel reads the same scope: validator + year + month + alert ID search. */
    private function scoped()
    {
        return DB::table('alerts')
            ->where('alerts.analisId', $this->analisId)
            ->where('alerts.isActive', 1)
            ->when($this->yearAlert !== 'all', fn ($q) => $q->whereYear('alerts.detectionDate', $this->yearAlert))
            ->when($this->monthAlert !== 'all', fn ($q) => $q->whereMonth('alerts.detectionDate', $this->monthAlert))
            ->when($this->hasSearch(), fn ($q) => $q->where('alerts.alertId', 'like', '%' . $this->searchId . '%'));
    }

    private function hasSearch()
    {
        return $this->searchId !== null && $this->searchId !== '';
    }

    /** Blank auditorStatus is a pending alert, not a status of its own. */
    private const STATUS_EXPR = "LOWER(COALESCE(NULLIF(alerts.auditorStatus, ''), 'pending'))";

    public function getStatusStats()
    {
        return $this->scoped()
            ->selectRaw(self::STATUS_EXPR . " AS status, COUNT(*) AS total")
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    public function getChartStats()
    {
        if ($this->yearAlert !== 'all') {
            $rows = $this->scoped()->selectRaw("MONTH(alerts.detectionDate) AS k, COUNT(*) AS c")
                ->groupBy('k')->orderBy('k')->pluck('c', 'k');

            return [
                'unit' => 'month',
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'keys' => range(1, 12),
                'values' => collect(range(1, 12))->map(fn ($m) => (int) ($rows[$m] ?? 0))->all(),
            ];
        }

        $rows = $this->scoped()->selectRaw("YEAR(alerts.detectionDate) AS k, COUNT(*) AS c")
            ->groupBy('k')->orderBy('k')->pluck('c', 'k');

        return [
            'unit' => 'year',
            'labels' => $rows->keys()->map(fn ($y) => (string) $y)->all(),
            'keys' => $rows->keys()->map(fn ($y) => (string) $y)->all(),
            'values' => $rows->values()->map(fn ($c) => (int) $c)->all(),
        ];
    }

    /**
     * Status x region matrix. Columns come from the data, ordered by the canonical
     * region list, so a region outside that list still gets a column instead of
     * being silently dropped.
     */
    public function getMatrix()
    {
        $canonical = ['Bali & Nusa Tenggara', 'Java', 'Kalimantan', 'Maluku', 'Papua', 'Sulawesi', 'Sumatra'];

        $rows = $this->scoped()
            ->selectRaw(self::STATUS_EXPR . " AS status, alerts.region AS region, COUNT(*) AS total")
            ->groupBy('status', 'alerts.region')
            ->get();

        $present = $rows->pluck('region')->unique()->filter()->values();
        $regions = collect($canonical)->intersect($present)
            ->merge($present->diff($canonical))->values()->all();

        $cells = [];
        foreach ($rows as $r) {
            $cells[$r->status][$r->region] = (int) $r->total;
        }

        return ['regions' => $regions, 'cells' => $cells];
    }

    /**
     * The validator's own work, in the "Alert by Validator" shape: one row per
     * action, one column per period. Auditor entries are excluded - this page
     * monitors the validator, not whoever reviewed them.
     * Scoped on the action date so the panel answers "what was done when".
     *
     * Counts DISTINCT alertId to match the dashboard's "Alert by Validator"
     * panel, which counts alerts touched rather than log rows written.
     */
    public function getActivityMatrix()
    {
        $byMonth = $this->yearAlert !== 'all';
        $periodExpr = $byMonth ? 'MONTH(l.created_at)' : 'YEAR(l.created_at)';

        $rows = DB::table('auditorlog as l')
            ->join('alerts as a', 'a.alertId', '=', 'l.alertId')
            ->where('a.analisId', $this->analisId)
            ->where('a.isActive', 1)
            ->whereColumn('l.auditorId', 'a.analisId')
            ->when($this->yearAlert !== 'all', fn ($q) => $q->whereYear('l.created_at', $this->yearAlert))
            ->when($this->monthAlert !== 'all', fn ($q) => $q->whereMonth('l.created_at', $this->monthAlert))
            ->when($this->hasSearch(), fn ($q) => $q->where('a.alertId', 'like', '%' . $this->searchId . '%'))
            ->selectRaw("{$periodExpr} AS period, l.ngapain AS action, COUNT(DISTINCT l.alertId) AS total")
            ->groupBy('period', 'action')
            ->get();

        $labelFor = [
            'Insert' => 'Insert',
            'refined' => 'Refined',
            'reclassification' => 'Re-classification',
            'reexportimage' => 'Re-export image',
            'Reject' => 'Reject',
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
        if (session('role_id') == 2) {
            abort(403);
        }

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
        $statusStats = $this->getStatusStats();
        $chart = $this->getChartStats();
        $matrix = $this->getMatrix();
        $activity = $this->getActivityMatrix();
        return view('livewire.alert-analis-component', compact('databases', 'analisName', 'statusStats', 'chart', 'matrix', 'activity'));
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
