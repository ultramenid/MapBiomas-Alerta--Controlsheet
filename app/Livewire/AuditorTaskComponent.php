<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class AuditorTaskComponent extends Component
{
    public $startDate, $endDate , $rangeAuditor;

    public function mount(){
        $this->startDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $this->endDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $this->rangeAuditor = $this->startDate.' to '.$this->endDate;
    }

    #[On('echo:analis-data,UpdateAnalis')]
    #[On('echo:auditor-data,UpdateAuditor')]
    #[On('brush-changed')]
    public function filter($start = null, $end = null){
        // brush-changed: update date range from the chart slider
        if ($start && $end) {
            $this->startDate = $start;
            $this->endDate = $end;
            $this->rangeAuditor = $start . ' to ' . $end;
        }

        // [Y-m-d => alerts audited], newest first, zero-filled across the range
        $counts = DB::table('auditorlog')
            ->select(DB::raw("DATE(created_at) as d"), DB::raw("COUNT(DISTINCT alertId) as total"))
            ->whereBetween(DB::raw("DATE(created_at)"), [$this->startDate, $this->endDate])
            ->where('ngapain', 'auditing')
            ->where('auditorId', session('id'))
            ->groupBy(DB::raw("DATE(created_at)"))
            ->pluck('total', 'd');

        $results = [];
        foreach (new \DatePeriod(
            new \DateTime($this->startDate),
            new \DateInterval('P1D'),
            (new \DateTime($this->endDate))->modify('+1 day')
        ) as $dt) {
            $d = $dt->format('Y-m-d');
            $results[$d] = (int) ($counts[$d] ?? 0);
        }

        return array_reverse($results, true);
    }

    public function render()
    {
        $results = $this->filter();

        return view('livewire.auditor-task-component', compact('results'));
    }
}
