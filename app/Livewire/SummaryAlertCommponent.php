<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class SummaryAlertCommponent extends Component
{

    public $yearAlert;

    public function mount(){
        $this->yearAlert = 'all';
    }

    #[On('filterYear')]
    public function updateData($year)
    {
        $this->yearAlert = $year;
    }

    #[On('echo:analis-data,UpdateAnalis')]
    #[On('echo:auditor-data,UpdateAuditor')]
    public function getAlerts(){
        $query = DB::table('alerts')
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
            return $q->whereYear('alerts.detectionDate', $this->yearAlert);
        })
        ->where('alerts.isActive', 1)
        ->where('users.is_active', 1)
        ->groupBy('alerts.auditorStatus')
        ->get();

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
        // dd($this->getAlerts());
        $alerts = $this->getAlerts();
        return view('livewire.summary-alert-commponent', compact('alerts'));
    }
}
