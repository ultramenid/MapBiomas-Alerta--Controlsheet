<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlertController extends Controller
{
    public function addalert(){
        $title = 'Add alert - Mapbiomas Indonesia';
        $nav = 'dashboard';
        return view('addalert', compact('title', 'nav'));
    }
    public function index(){
        $title = 'Alerts - Mapbiomas Indonesia';
        $nav = 'alerts';
        return view('alerts', compact('title', 'nav'));

    }

    public function auditorAlert($id){
        $id = $id;
        $title = 'Auditor alert - Mapbiomas Indonesia';
        $nav = 'alerts';
        return view('auditor-alert', compact('id', 'title', 'nav'));
    }

    public function auditing($id){
        $id = $id;
        $title = 'Auditing alert - Mapbiomas Indonesia';
        $nav = 'alerts';
        return view('auditing', compact('id', 'title', 'nav'));
    }

    public function checkAnalis($id){
        return DB::table('alerts')->where('alertId', $id)->where('isActive', 1)->first();
    }

    public function editalert($id){
        if(!$this->checkAnalis($id) or $this->checkAnalis($id)->analisId != session('id') or $this->checkAnalis($id)->auditorStatus == 'approved' or $this->checkAnalis($id)->auditorStatus == 'rejected' or $this->checkAnalis($id)->auditorStatus == 'duplicate'){
            return redirect('alerts');
        }
        $id = $id;
        $title = 'Edit alert - Mapbiomas Indonesia';
        $nav = 'alerts';
        return view('editalert', compact('id', 'title', 'nav'));
    }

    public function alertanalis($id){
        $id = $id;
        $title = 'Alert analis - Mapbiomas Indonesia';
        $nav = 'alerts';
        return view('alertanalis', compact('id', 'title', 'nav'));
    }

    public function fix($id){
        return DB::table('alerts')
        ->where('alertId', $id)
        ->where('isActive', 1)
        ->select(
            'alertId',
            'auditorStatus',
            'auditorReason'
        )
        ->first();
    }

    public function audit($id){

        $data = DB::table('alerts')
        ->join('users', 'alerts.analisId', '=', 'users.id')
        ->select([
            'alerts.alertId',
            'alerts.auditorStatus',
            'alerts.alertStatus',
            'alerts.auditorReason',
            'alerts.observation',
            'alerts.alertNote',
            'users.name as name',
        ])
        ->where('alerts.isActive', 1)
        ->where('alerts.id', $id)
        ->first();
        // dd($data);

        return $data;

    }

    public function alertsTest(){
        $title = 'Auditor alert - Mapbiomas Indonesia';
        $nav = 'alerts-test';
        return view('alerts-test', compact('title', 'nav'));
    }
}
