<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ValidatorExport implements FromCollection, WithHeadings
{

    private $status;
    private $year;
    private $month;
    private $analisId;

    public function __construct($status, $year, $month, $analisId)
    {
        $this->status = $status;
        $this->year  = $year;
        $this->month = $month;
        $this->analisId = $analisId;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
         return DB::table('alerts')
        ->join('users', 'alerts.analisId', '=', 'users.id')
        ->select('alerts.alertId as alertId', 'users.name as validator','alerts.alertStatus as alertStatus', 'alerts.detectionDate as detectionDate', 'alerts.observation as observation', 'alerts.region as region', 'alerts.province as province','alerts.auditorStatus as auditorStatus', 'alerts.created_at as inputDate' )
        ->when($this->status != 'all', function ($query) {
            return $query->where('auditorStatus', $this->status);
        })
        ->when($this->year != 'all', function ($query) {
            return $query->whereYear('detectionDate', $this->year);
        })
        ->when($this->month != 'all', function ($query) {
            return $query->whereMonth('detectionDate', $this->month);
        })
        ->where('alerts.analisId', $this->analisId)
        ->get();
    }

    public function headings(): array
    {
        return [
            "Alert ID",
            "Validator",
            "Alert Status",
            "Detection Date",
            "Observation",
            "Region",
            "Province",
            "Auditor Status",
            "Input Date"
        ];
    }
}
