<?php

namespace App\Livewire;

use App\Livewire\Concerns\CachesAggregates;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class ValidatorTaskComponent extends Component
{
    use CachesAggregates;
    public $startDateValidator, $endDateValidator, $rangeValidator;
    // null = all users; set to scope the report to a single user
    public $scopeUserId = null;
    public $report = [
        "dates" => [],
        "data" => [],
    ];

    public function mount($scopeUserId = null)
    {
        // only admins may scope to "all"; everyone else is locked to themselves
        $role = (int) session('role_id');
        $this->scopeUserId = $role === 0
            ? ($scopeUserId ?? null)
            : ($role === 2 ? session('id') : null);

        $this->startDateValidator = Carbon::now("Asia/Jakarta")->format(
            "Y-m-d",
        );
        $this->endDateValidator = Carbon::now("Asia/Jakarta")->format("Y-m-d");
        $this->rangeValidator =
            $this->startDateValidator . " to " . $this->endDateValidator;
        $this->generateReport();
    }

    #[On("echo:analis-data,UpdateAnalis")]
    #[On("echo:auditor-data,UpdateAuditor")]
    #[On("brush-changed")]
    public function refreshReport($start = null, $end = null)
    {
        // brush-changed: update date range from the chart slider, then regenerate
        if ($start && $end) {
            $this->startDateValidator = $start;
            $this->endDateValidator = $end;
            $this->rangeValidator = $start . " to " . $end;
        }
        $this->forgetCached($this->tasksCacheKey());
        $this->forgetCached($this->approvedCacheKey());
        $this->generateReport();
    }

    private function tasksCacheKey()
    {
        return "dashboard:validator-task:tasks:v2:" . $this->startDateValidator . ":" . $this->endDateValidator . ":" . ($this->scopeUserId ?? "all");
    }

    private function approvedCacheKey()
    {
        return "dashboard:validator-task:approved:v2:" . $this->startDateValidator . ":" . $this->endDateValidator . ":" . ($this->scopeUserId ?? "all");
    }

    public function generateReport()
    {
        // re-clamp: scopeUserId is a public property, so a non-admin could
        // tamper it via the request payload; enforce the boundary every query.
        $role = (int) session('role_id');
        if ($role !== 0) {
            $this->scopeUserId = ($role === 2 ? session('id') : null);
        }

        /*
    |--------------------------------------------------------------------------
    | QUERY TASK (auditorlog)
    |--------------------------------------------------------------------------
    */
        $rows = $this->cached($this->tasksCacheKey(), 60, function () {
            return DB::table("auditorlog")
                ->join("users", "users.id", "=", "auditorlog.auditorId")
                ->select(
                    "users.name as validatorName",
                    "users.id as auditorId",
                    DB::raw("DATE(auditorlog.created_at) as d"),

                    // same definition as the chart's validator series, so the
                    // card's two halves agree; the chips below break it down
                    DB::raw(
                        "COUNT(DISTINCT CASE WHEN auditorlog.ngapain IN ('Insert', 'Reject', 'reclassification', 'reexportimage', 'refined') THEN auditorlog.alertId END) as total",
                    ),

                    DB::raw(
                        "COUNT(DISTINCT CASE WHEN auditorlog.ngapain = 'Insert' THEN auditorlog.alertId END) as total_Insert",
                    ),
                    DB::raw(
                        "COUNT(DISTINCT CASE WHEN auditorlog.ngapain = 'Reject' THEN auditorlog.alertId END) as total_Reject",
                    ),
                    DB::raw(
                        "COUNT(DISTINCT CASE WHEN auditorlog.ngapain = 'reclassification' THEN auditorlog.alertId END) as total_reclassification",
                    ),
                    DB::raw(
                        "COUNT(DISTINCT CASE WHEN auditorlog.ngapain = 'reexportimage' THEN auditorlog.alertId END) as total_reexportimage",
                    ),
                    DB::raw(
                        "COUNT(DISTINCT CASE WHEN auditorlog.ngapain = 'refined' THEN auditorlog.alertId END) as total_refined",
                    ),
                )
                ->whereBetween("auditorlog.created_at", [
                    $this->startDateValidator . " 00:00:00",
                    $this->endDateValidator . " 23:59:59",
                ])
                ->where("users.is_active", 1)
                ->when($this->scopeUserId, fn($q) => $q->where("auditorlog.auditorId", $this->scopeUserId))
                ->whereIn("auditorlog.ngapain", [
                    "Insert",
                    "Reject",
                    "reclassification",
                    "reexportimage",
                    "refined",
                ])
                ->groupBy(
                    "users.name",
                    "users.id",
                    DB::raw("DATE(auditorlog.created_at)"),
                )
                ->orderBy("users.name")
                ->get();
        });

        /*
    |--------------------------------------------------------------------------
    | QUERY APPROVED (alerts)
    |--------------------------------------------------------------------------
    */
        $approvedRows = $this->cached($this->approvedCacheKey(), 60, function () {
            $q = DB::table("alerts")
                ->select(
                    "alerts.analisId as auditorId",
                    DB::raw("DATE(alerts.updated_at) as d"),
                    DB::raw("COUNT(DISTINCT alerts.alertId) as approvedTotal"),
                )
                ->whereBetween("alerts.updated_at", [
                    $this->startDateValidator . " 00:00:00",
                    $this->endDateValidator . " 23:59:59",
                ])
                ->where("alerts.auditorStatus", "approved")
                ->when($this->scopeUserId, fn($q) => $q->where("alerts.analisId", $this->scopeUserId))
                ->groupBy("alerts.analisId", DB::raw("DATE(alerts.updated_at)")) 
                ->get();
            return $q;
        });

        /*
    |--------------------------------------------------------------------------
    | CONVERT APPROVED KE MAP
    |--------------------------------------------------------------------------
    */
        $approvedMap = [];

        foreach (empty($approvedRows) ? [] : $approvedRows as $row) {
            $approvedMap[$row->auditorId][$row->d] = $row->approvedTotal;
        }

        /*
    |--------------------------------------------------------------------------
    | BUILD RESULT
    |--------------------------------------------------------------------------
    */
        $results = [];

        // $rows can be null when the cache stores an empty result; normalise
        // before iterating so an unanswered range doesn't fatal the page
        foreach (empty($rows) ? [] : $rows as $row) {
            if (!isset($results[$row->auditorId])) {
                $results[$row->auditorId] = [
                    "validatorName" => $row->validatorName,
                    "auditorId" => $row->auditorId,

                    "dates" => [],

                    "category" => [
                        "Insert" => 0,
                        "Reject" => 0,
                        "reclassification" => 0,
                        "reexportimage" => 0,
                        "refined" => 0,
                        "approved" => 0,
                    ],

                    "grandTotal" => 0,
                    "grandApproved" => 0,
                ];
            }

            /*
        |--------------------------------------------------------------------------
        | TASK & APPROVED PER DATE
        |--------------------------------------------------------------------------
        */
            $approved = $approvedMap[$row->auditorId][$row->d] ?? 0;

            $results[$row->auditorId]["dates"][$row->d] = [
                "task" => $row->total,
                "approved" => $approved,
            ];

            /*
        |--------------------------------------------------------------------------
        | CATEGORY TOTAL
        |--------------------------------------------------------------------------
        */
            $results[$row->auditorId]["category"]["Insert"] +=
                $row->total_Insert;
            $results[$row->auditorId]["category"]["Reject"] +=
                $row->total_Reject;
            $results[$row->auditorId]["category"]["refined"] +=
                $row->total_refined;
            $results[$row->auditorId]["category"]["reclassification"] +=
                $row->total_reclassification;
            $results[$row->auditorId]["category"]["reexportimage"] +=
                $row->total_reexportimage;
            $results[$row->auditorId]["category"]["approved"] += $approved;

            /*
        |--------------------------------------------------------------------------
        | GRAND TOTAL
        |--------------------------------------------------------------------------
        */
            $results[$row->auditorId]["grandTotal"] += $row->total;
            $results[$row->auditorId]["grandApproved"] += $approved;
        }

        /*
    |--------------------------------------------------------------------------
    | FILL THE RANGE so every row has a cell per day, gaps included
    |--------------------------------------------------------------------------
    */
        $allDates = $this->dates();
        foreach ($results as &$row) {
            $filled = [];
            foreach ($allDates as $d) {
                $filled[$d] = $row["dates"][$d] ?? ["task" => 0, "approved" => 0];
            }
            $row["dates"] = $filled;
        }
        unset($row);

        // busiest validators first — the ranked order the card reads top-down
        uasort($results, fn($a, $b) => $b["grandTotal"] <=> $a["grandTotal"]);

        $this->report = [
            "dates" => $allDates,
            "data" => $results,
        ];
    }

    /** Every date in the selected range, ascending. */
    private function dates(): array
    {
        $out = [];
        foreach (
            new \DatePeriod(
                new \DateTime($this->startDateValidator),
                new \DateInterval("P1D"),
                (new \DateTime($this->endDateValidator))->modify("+1 day"),
            )
            as $dt
        ) {
            $out[] = $dt->format("Y-m-d");
        }
        return $out;
    }

    public function render()
    {
        return view("livewire.validator-task-component");
    }
}
