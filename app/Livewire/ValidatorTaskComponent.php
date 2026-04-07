<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class ValidatorTaskComponent extends Component
{
    public $startDateValidator, $endDateValidator , $rangeValidator;
    public $report = [
        'dates' => [],
        'data'  => []
    ];

    public function updatedrangeValidator()
    {
        $this->generateReport();
    }

    public function mount(){
        $this->startDateValidator = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $this->endDateValidator = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $this->rangeValidator = $this->startDateValidator.' to '.$this->endDateValidator;
        $this->generateReport();
    }


    #[On('echo:analis-data,UpdateAnalis')]
    #[On('echo:auditor-data,UpdateAuditor')]
    public function generateReport()
{
    /*
    |--------------------------------------------------------------------------
    | QUERY TASK (auditorlog)
    |--------------------------------------------------------------------------
    */
    $rows = DB::table('auditorlog')
        ->join('users', 'users.id', '=', 'auditorlog.auditorId')
        ->select(
            'users.name as validatorName',
            'users.id as auditorId',
            DB::raw("DATE(auditorlog.created_at) as d"),

            DB::raw("COUNT(DISTINCT auditorlog.alertId) as total"),

            DB::raw("COUNT(DISTINCT CASE WHEN auditorlog.ngapain = 'Insert' THEN auditorlog.alertId END) as total_Insert"),
            DB::raw("COUNT(DISTINCT CASE WHEN auditorlog.ngapain = 'Reject' THEN auditorlog.alertId END) as total_Reject"),
            DB::raw("COUNT(DISTINCT CASE WHEN auditorlog.ngapain = 'reclassification' THEN auditorlog.alertId END) as total_reclassification"),
            DB::raw("COUNT(DISTINCT CASE WHEN auditorlog.ngapain = 'reexportImage' THEN auditorlog.alertId END) as total_reexportimage"),
            DB::raw("COUNT(DISTINCT CASE WHEN auditorlog.ngapain = 'refined' THEN auditorlog.alertId END) as total_refined")
        )
        ->whereBetween(DB::raw("DATE(auditorlog.created_at)"), [$this->startDateValidator, $this->endDateValidator])
        ->where('users.is_active', 1)
        ->whereIn('auditorlog.ngapain', [
            'Insert',
            'Reject',
            'reclassification',
            'reexportImage',
            'refined'
        ])
        ->groupBy('users.name', 'users.id', DB::raw("DATE(auditorlog.created_at)"))
        ->orderBy('users.name')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | QUERY APPROVED (alerts)
    |--------------------------------------------------------------------------
    */
    $approvedRows = DB::table('alerts')
        ->select(
            'alerts.analisId as auditorId',
            DB::raw("DATE(alerts.updated_at) as d"),
            DB::raw("COUNT(DISTINCT alerts.alertId) as approvedTotal")
        )
        ->whereBetween(DB::raw("DATE(alerts.updated_at)"), [$this->startDateValidator, $this->endDateValidator])
        ->where('alerts.auditorStatus', 'approved')
        ->groupBy('alerts.analisId', DB::raw("DATE(alerts.updated_at)"))
        ->get();


    /*
    |--------------------------------------------------------------------------
    | CONVERT APPROVED KE MAP
    |--------------------------------------------------------------------------
    */
    $approvedMap = [];

    foreach ($approvedRows as $row) {

        $approvedMap[$row->auditorId][$row->d] = $row->approvedTotal;

    }


    /*
    |--------------------------------------------------------------------------
    | BUILD RESULT
    |--------------------------------------------------------------------------
    */
    $results = [];
    $dates   = [];

    foreach ($rows as $row) {

        $dates[$row->d] = $row->d;

        if (!isset($results[$row->auditorId])) {

            $results[$row->auditorId] = [

                'validatorName' => $row->validatorName,
                'auditorId'     => $row->auditorId,

                'dates' => [],

                'category' => [
                    'Insert'            => 0,
                    'Reject'            => 0,
                    'reclassification'  => 0,
                    'reexportimage'     => 0,
                    'refined'           => 0,
                    'approved'          => 0
                ],

                'grandTotal'    => 0,
                'grandApproved' => 0

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | TASK & APPROVED PER DATE
        |--------------------------------------------------------------------------
        */
        $approved = $approvedMap[$row->auditorId][$row->d] ?? 0;

        $results[$row->auditorId]['dates'][$row->d] = [

            'task'     => $row->total,
            'approved' => $approved

        ];


        /*
        |--------------------------------------------------------------------------
        | CATEGORY TOTAL
        |--------------------------------------------------------------------------
        */
        $results[$row->auditorId]['category']['Insert'] += $row->total_Insert;
        $results[$row->auditorId]['category']['Reject'] += $row->total_Reject;
        $results[$row->auditorId]['category']['refined'] += $row->total_refined;
        $results[$row->auditorId]['category']['reclassification'] += $row->total_reclassification;
        $results[$row->auditorId]['category']['reexportimage'] += $row->total_reexportimage;
        $results[$row->auditorId]['category']['approved'] += $approved;


        /*
        |--------------------------------------------------------------------------
        | GRAND TOTAL
        |--------------------------------------------------------------------------
        */
        $results[$row->auditorId]['grandTotal'] += $row->total;
        $results[$row->auditorId]['grandApproved'] += $approved;

    }


    /*
    |--------------------------------------------------------------------------
    | SORT DATE
    |--------------------------------------------------------------------------
    */
    ksort($dates);


    /*
    |--------------------------------------------------------------------------
    | FINAL RESULT
    |--------------------------------------------------------------------------
    */
    $this->report = [

        'dates' => array_values($dates),
        'data'  => $results

    ];
}

    public function render()
    {
        return view('livewire.validator-task-component');
    }
}
