<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Livewire\Concerns\CachesAggregates;
use Livewire\Attributes\On;
use Masmerise\Toaster\Toaster;

class AuditorSummaryComponent extends Component
{
    use CachesAggregates;
    public $startDate , $endDate, $rangeAuditor, $alertCode, $alertCodeValidator;


    // Bind to UI
    public string $dataField = 'total';   // users.name | d | total | users.id
    public string $dataOrder = 'desc';    // asc | desc


    // Allowlist to keep it safe
    private array $allowedFields = ['users.name', 'users.id', 'd', 'total'];
    private array $allowedOrders = ['asc', 'desc'];

    private function normalizedSort(): array
    {
        $field = in_array($this->dataField, $this->allowedFields, true) ? $this->dataField : 'total';
        $order = in_array(strtolower($this->dataOrder), $this->allowedOrders, true) ? strtolower($this->dataOrder) : 'desc';
        return [$field, $order];
    }

    public function sortBy(string $field): void
    {
        $field = trim($field); // <- important for date keys like '2025-10-31'

        if ($this->dataField === $field) {
            $this->dataOrder = $this->dataOrder === 'asc' ? 'desc' : 'asc';
        } else {
            $this->dataField = $field;
            // default direction: totals & dates → desc, names/ids → asc
            $this->dataOrder = ($field === 'Total' || $this->isYmd($field)) ? 'desc'
                            : (in_array($field, ['users.name', 'users.id'], true) ? 'asc' : 'desc');
        }
    }

    private function isYmd(string $s): bool
    {
        // accept exactly YYYY-MM-DD
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $s);
    }

    public function mount(){
        $this->startDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $this->endDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $this->rangeAuditor = $this->startDate.' to '.$this->endDate;
    }



    public function find(){
        // ngapain='auditing' only — auditorlog also stores validator actions
        // (Insert/Reject/refined/reexportimage/reclassification), so without
        // this filter the latest row is often the validator, not the auditor.
        $find = DB::table('auditorlog')
            ->where('alertId', $this->alertCode)
            ->where('ngapain', 'auditing')
            ->join('users', 'users.id', '=', 'auditorlog.auditorId')
            ->select('users.name as auditorName', 'users.id as auditorId')
            ->orderBy('auditorlog.created_at', 'desc')
            ->first();

        if (! $find) {
            $exists = DB::table('auditorlog')->where('alertId', $this->alertCode)->exists();
            Toaster::error($exists
                ? 'Alert ID '.$this->alertCode.' has not been audited yet'
                : 'Alert ID '.$this->alertCode.' not found in auditor log');
            return;
        }

        Toaster::success('Alert ID '.$this->alertCode.' audited by '.$find->auditorName);
    }

    public function getStatus($alertId){
        // if the auditorStatus == null return 'Pending'
        $status = DB::table('alerts')
            ->where('alertId', $alertId)
            ->where('isActive', 1)
            ->select('auditorStatus')
            ->first();
        if($status->auditorStatus == null){
            return 'pending';
        }else{
            return $status->auditorStatus ;
        }
    }
    public function findValidator(){
        // dd($this->getStatus($this->alertCodeValidator));
        $find = DB::table('alerts')
            ->where('alertId', $this->alertCodeValidator)
            ->where('isActive', 1)
            ->join('users', 'users.id', '=', 'alerts.analisId')
            ->select('users.name as auditorName', 'users.id as auditorId')
            ->first();

        if (! $find) {
            Toaster::error('Alert ID '.$this->alertCodeValidator.' not found in alert database');
            return;
        }

        Toaster::success('Alert ID '.$this->alertCodeValidator.' validated by '.$find->auditorName. ' with status '.$this->getStatus($this->alertCodeValidator));
    }

    private function cacheKey(){
        // the row set depends on the date range and the sort applied in SQL
        return 'dashboard:auditor-summary:v1:'.$this->startDate.':'.$this->endDate.':'.$this->dataField.':'.$this->dataOrder;
    }

    #[On('echo:analis-data,UpdateAnalis')]
    #[On('echo:auditor-data,UpdateAuditor')]
    public function refreshAuditorSummary(){
        // realtime events must show through the 60s cache window; render()
        // calls filter() which repopulates the cache with fresh data
        $this->forgetCached($this->cacheKey());
    }

    public function filter(){

       [$dataField, $dataOrder] = $this->normalizedSort();

        $rows = $this->cached($this->cacheKey(), 60, function () use ($dataField, $dataOrder) {
            return DB::table('auditorlog')
        ->join('users', 'users.id', '=', 'auditorlog.auditorId')
        ->select(
            'users.name as auditorName',
            'users.id as auditorId',
            DB::raw("DATE(auditorlog.created_at) as d"),
            DB::raw("COUNT(DISTINCT auditorlog.alertId) as total")
        )
        ->whereBetween('auditorlog.created_at', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59'])
        ->where('ngapain', 'auditing')
        ->where('users.is_active', 1)
        ->groupBy('users.name', 'users.id', DB::raw("DATE(auditorlog.created_at)"))
        ->orderBy($dataField, $dataOrder)
        ->get();
        });



        $results = [];
        foreach ($rows as $row) {
            if (!isset($results[$row->auditorName])) {
                $results[$row->auditorName]['auditorName'] = $row->auditorName;
                $results[$row->auditorName]['auditorId']   = $row->auditorId;
            }
            $results[$row->auditorName][$row->d] = (int) $row->total;
        }

        $period = new \DatePeriod(
            new \DateTime($this->startDate),
            new \DateInterval('P1D'),
            (new \DateTime($this->endDate))->modify('+1 day')
        );

        $allDates = [];
        foreach ($period as $dt) {
            $allDates[] = $dt->format('Y-m-d');
        }

        foreach ($results as &$row) {
            $total = 0;
            foreach ($allDates as $d) {
                if (!isset($row[$d])) $row[$d] = 0;
                $total += $row[$d];
            }
            $ordered = [
                'auditorName' => $row['auditorName'],
                'auditorId'   => $row['auditorId'],
            ];
            foreach ($allDates as $d) $ordered[$d] = $row[$d];
            $ordered['Total'] = $total;
            $row = $ordered;
        }
        unset($row);



        if ($this->dataField === 'Total') {
            usort($results, fn($a, $b) =>
                $this->dataOrder === 'asc' ? $a['Total'] <=> $b['Total'] : $b['Total'] <=> $a['Total']
            );
        } elseif ($this->isYmd($this->dataField)) {
            $k = $this->dataField;        // e.g. '2025-10-31'
            usort($results, fn($a, $b) =>  // note: treat missing as 0
                $this->dataOrder === 'asc'
                    ? ((int)($a[$k] ?? 0)) <=> ((int)($b[$k] ?? 0))
                    : ((int)($b[$k] ?? 0)) <=> ((int)($a[$k] ?? 0))
            );
        }

        // return numerically indexed array
        return array_values($results);

    }



    public function render()
    {
        // dd($this->filter());
        $results = $this->filter();
        $dataField = $this->dataField;
        $dataOrder = $this->dataOrder;
        return view('livewire.auditor-summary-component', compact('results', 'dataField', 'dataOrder'));
    }
}
