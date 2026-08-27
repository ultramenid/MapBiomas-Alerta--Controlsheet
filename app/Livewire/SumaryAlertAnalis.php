<?php

namespace App\Livewire;

use App\Livewire\Concerns\CachesAggregates;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class SumaryAlertAnalis extends Component
{
    use CachesAggregates;

    public $yearAlert;
    public $monthAlert;

    private function cacheKey(){
        // data is scoped to the signed-in analis, so the key must be too
        return 'dashboard:summary-analis:v1:'.session('id').':'.$this->yearAlert.':'.$this->monthAlert;
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
    #[On('echo:auditor-data,UpdateAuditor')]
    public function refreshAlerts(){
        // realtime events must show through the 60s cache window
        $this->forgetCached($this->cacheKey());
    }

    public function getAlerts(){
        $query = $this->cached($this->cacheKey(), 60, function () {
            return DB::table('alerts')
            ->join('users', 'users.id', '=', 'alerts.analisId')
            ->selectRaw("
                COALESCE(alerts.auditorStatus, 'Pending') AS auditorStatus,
                SUM(CASE WHEN alerts.region = 'Bali & Nusa Tenggara' THEN 1 ELSE 0 END) AS `Balinusatenggara`,
                SUM(CASE WHEN alerts.region = 'Java' THEN 1 ELSE 0 END) AS `Java`,
                SUM(CASE WHEN alerts.region = 'Kalimantan' THEN 1 ELSE 0 END) AS `Kalimantan`,
                SUM(CASE WHEN alerts.region = 'Maluku' THEN 1 ELSE 0 END) AS `Maluku`,
                SUM(CASE WHEN alerts.region = 'Papua' THEN 1 ELSE 0 END) AS `Papua`,
                SUM(CASE WHEN alerts.region = 'Sulawesi' THEN 1 ELSE 0 END) AS `Sulawesi`,
                SUM(CASE WHEN alerts.region = 'Sumatra' THEN 1 ELSE 0 END) AS `Sumatra`,
                COUNT(*) AS `TOTAL`
            ")
            ->when($this->yearAlert !== 'all', function ($q) {
                // detectionDate is a 'YYYY-MM-DD' string, so an explicit range
                // compares lexicographically and stays sargable
                $start = $this->yearAlert.'-01-01';
                $end = $this->yearAlert.'-12-31';
                if ($this->monthAlert !== 'all') {
                    $month = Carbon::createFromDate((int) $this->yearAlert, (int) $this->monthAlert, 1);
                    $start = $month->copy()->startOfMonth()->toDateString();
                    $end = $month->copy()->endOfMonth()->toDateString();
                }
                return $q->whereBetween('alerts.detectionDate', [$start, $end]);
            })
            ->when($this->yearAlert === 'all' && $this->monthAlert !== 'all', function ($q) {
                // month-only across every year cannot be a single sargable range
                return $q->whereMonth('alerts.detectionDate', $this->monthAlert);
            })
            ->where('alerts.isActive', 1)
            ->where('alerts.analisId', session('id'))
            ->where('users.is_active', 1)   // only include active users
            ->groupBy('alerts.auditorStatus')
            ->get();
        });



    // Add Grand Total manually
    $grandTotal = [
        'auditorStatus' => 'Grand Total',
        'Balinusatenggara' => $query->sum('Balinusatenggara'),
        'Java' => $query->sum('Java'),
        'Kalimantan' => $query->sum('Kalimantan'),
        'Maluku' => $query->sum('Maluku'),
        'Papua' => $query->sum('Papua'),
        'Sulawesi' => $query->sum('Sulawesi'),
        'Sumatra' => $query->sum('Sumatra'),
        'TOTAL' => $query->sum('TOTAL'),
    ];

    // Convert Laravel collection to array of associative arrays
    $finalResults = json_decode(json_encode($query), true);

    // Append Grand Total
    $finalResults[] = $grandTotal;

    // Sort manually: Place "Pending" first, then by TOTAL descending, and "Grand Total" at the end
    usort($finalResults, function ($a, $b) {
        if ($a['auditorStatus'] === 'Grand Total') return 1;
        if ($b['auditorStatus'] === 'Grand Total') return -1;
        if ($a['auditorStatus'] === 'Pending') return -1;
        if ($b['auditorStatus'] === 'Pending') return 1;
        return $b['TOTAL'] <=> $a['TOTAL'];
    });

    // Return the final sorted result
    return $finalResults;

    }
    public function render()
    {
        $alerts = $this->getAlerts();
        return view('livewire.sumary-alert-analis', compact('alerts'));
    }
}
