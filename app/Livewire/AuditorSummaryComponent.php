<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Livewire\Concerns\CachesAggregates;
use Livewire\Attributes\On;

class AuditorSummaryComponent extends Component
{
    use CachesAggregates;
    public $startDate , $endDate, $rangeAuditor;

    // sort the ranked list: name | total
    public string $dataField = 'total';
    public string $dataOrder = 'desc';

    public function sortBy(string $field): void
    {
        // 'name', 'total', or a single day column ('2026-08-19')
        $field = trim($field);
        if (! in_array($field, ['name', 'total'], true) && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $field)) {
            $field = 'total';
        }

        if ($this->dataField === $field) {
            $this->dataOrder = $this->dataOrder === 'asc' ? 'desc' : 'asc';
        } else {
            $this->dataField = $field;
            $this->dataOrder = $field === 'name' ? 'asc' : 'desc';
        }
    }

    public function mount(){
        $this->startDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $this->endDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $this->rangeAuditor = $this->startDate.' to '.$this->endDate;
    }



    private function cacheKey(){
        // sorting happens in PHP now, so the cached row set depends only on the range
        return 'dashboard:auditor-summary:v2:'.$this->startDate.':'.$this->endDate;
    }

    #[On('echo:analis-data,UpdateAnalis')]
    #[On('echo:auditor-data,UpdateAuditor')]
    #[On('brush-changed')]
    public function refreshAuditorSummary($start = null, $end = null)
    {
        // brush-changed event carries the chart's selected date range;
        // echo events just invalidate the cache and re-render
        if ($start && $end) {
            $this->startDate = $start;
            $this->endDate = $end;
            $this->rangeAuditor = $start . ' to ' . $end;
            $this->forgetCached($this->cacheKey());
        }
    }

    /** One row per auditor: name, id, per-day counts, total. */
    public function filter(){
        $rows = $this->cached($this->cacheKey(), 60, function () {
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
                ->orderBy('users.name')
                ->get();
        });

        $results = [];
        foreach (empty($rows) ? [] : $rows as $row) {
            $results[$row->auditorId] ??= [
                'auditorName' => $row->auditorName,
                'auditorId'   => $row->auditorId,
                'daily'       => [],
                'total'       => 0,
            ];
            $results[$row->auditorId]['daily'][$row->d] = (int) $row->total;
            $results[$row->auditorId]['total'] += (int) $row->total;
        }

        // zero-fill the range so every day has a cell
        foreach ($results as &$row) {
            $daily = [];
            foreach ($this->dates() as $d) $daily[$d] = $row['daily'][$d] ?? 0;
            $row['daily'] = $daily;
        }
        unset($row);

        $dir = $this->dataOrder === 'asc' ? 1 : -1;
        $key = $this->dataField;
        usort($results, fn($a, $b) => $dir * match (true) {
            $key === 'name' => strcasecmp($a['auditorName'], $b['auditorName']),
            $key === 'total' => $a['total'] <=> $b['total'],
            default => ($a['daily'][$key] ?? 0) <=> ($b['daily'][$key] ?? 0),   // one day column
        });

        return $results;
    }

    /** Every date in the selected range, ascending. */
    private function dates(): array
    {
        $out = [];
        foreach (new \DatePeriod(
            new \DateTime($this->startDate),
            new \DateInterval('P1D'),
            (new \DateTime($this->endDate))->modify('+1 day')
        ) as $dt) {
            $out[] = $dt->format('Y-m-d');
        }
        return $out;
    }

    public function render()
    {
        $results = $this->filter();

        return view('livewire.auditor-summary-component', [
            'results'   => $results,
            'dates'     => $this->dates(),
            'dataField' => $this->dataField,
            'dataOrder' => $this->dataOrder,
        ]);
    }
}
