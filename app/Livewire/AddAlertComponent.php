<?php

namespace App\Livewire;

use App\Livewire\Concerns\CachesAggregates;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class AddAlertComponent extends Component
{
    use CachesAggregates;
    public $alertId, $alertStatus, $detectionDate, $observation, $alertNote;
    public $chooseRegion = '', $chooseProvince = '';
    public $region = 'Please select', $province = 'Please select';

    public function getRegions(){
        // full island list fetched once a day; the search box narrows it in
        // PHP so typing does not trigger a GeoServer round trip anymore.
        // A null return (GeoServer down) is not cached, so it retries later.
        $regions = $this->cached('geoserver:regions', 86400, function () {
            try {
                $req = Http::timeout(3)->retry(1, 100)->get('http://129.150.48.143:8080/geoserver/simontini/wfs',
                [
                    'service' => 'wfs',
                    'version' => '1.1.0',
                    'request' => 'GetFeature',
                    'typename' => 'simontini:province',
                    'propertyName' => 'island_en',
                    'maxFeatures' => 1000,
                    'outputFormat' => 'application/json',
                ]);
                $response = json_decode($req, true);
                $res = array();
                foreach ($response['features'] as $each) {
                    $res[$each['properties']['island_en']] = array($each['properties']['island_en']);
                }
                return $res ?: null;
            } catch (\Throwable $th) {
                return null;
            }
        }) ?? [];

        if ($this->chooseRegion !== '') {
            $regions = array_filter($regions, function ($value) {
                return stripos($value[0], $this->chooseRegion) !== false;
            });
        }

        return $regions;
    }

    public function getProvinces(){
        // provinces are only fetched once a region is actually selected,
        // then cached per region; the search box narrows them in PHP
        if ($this->region === '' || $this->region === 'Please select') {
            return [];
        }

        $region = str_replace("'", "''", (string) $this->region);
        $provincies = $this->cached('geoserver:provinces:'.$region, 86400, function () use ($region) {
            try {
                $req = Http::timeout(3)->retry(1, 100)->get('http://129.150.48.143:8080/geoserver/simontini/wfs',
                [
                    'service' => 'wfs',
                    'version' => '1.1.0',
                    'request' => 'GetFeature',
                    'typename' => 'simontini:province',
                    'propertyName' => 'island_en,prov_en',
                    'cql_filter' => "island_en = '". $region ."'",
                    'maxFeatures' => 1000,
                    'outputFormat' => 'application/json',
                ]);
                $response = json_decode($req, true);
                $res = array();
                foreach ($response['features'] as $each) {
                    $res[$each['properties']['prov_en']] = array($each['properties']['prov_en']);
                }
                return $res ?: null;
            } catch (\Throwable $th) {
                return null;
            }
        }) ?? [];

        if ($this->chooseProvince !== '') {
            $provincies = array_filter($provincies, function ($value) {
                return stripos($value[0], $this->chooseProvince) !== false;
            });
        }

        return $provincies;
    }

    public function selectRegion($value){
        // dd($value);
        $this->region = $value;
        $this->province = 'Please select';
        $this->dispatch('region', ['newName' => $value]);
        $this->chooseProvince = '';

    }

    public function selectProvince($value){
        // dd($value);
        $this->province = $value;
        $this->dispatch('province', ['newName' => $value]);

    }

    public function render()
    {
        $regions = $this->getRegions();
        $provincies = $this->getProvinces();
        return view('livewire.add-alert-component',compact('regions', 'provincies'));
    }

    public function checkAlert(){
        return DB::table('alerts')->where('alertId', $this->alertId)->where('isActive', 1)->first();
    }

    public function checkAlertStatus(){
        $status = 'pre-approved';

        if($this->alertStatus == 'rejected'){
            $status = 'rejected';
        }

        return $status;
    }

    public function storeAlert(){

        if($this->manualValidation()){
            DB::table('alerts')->insert([
                'analisId' => session('id'),
                'alertId' => $this->alertId,
                'observation' => $this->observation,
                'alertStatus' => $this->alertStatus,
                'detectionDate' => $this->detectionDate,
                'alertNote' => $this->alertNote,
                'region' => $this->region,
                'province' => $this->province,
                'auditorStatus' => $this->checkAlertStatus(),
                'isActive' => 1,
                'platformStatus' => 'sccon',
                'created_at' => Carbon::now('Asia/Jakarta')
            ]);

            DB::table('auditorlog')->insert([
                'auditorId' => session('id'),
                'alertId' => $this->alertId,
                'ngapain' =>  ($this->alertStatus == 'rejected') ? 'Reject' : 'Insert',
                'created_at' => Carbon::now('Asia/Jakarta')
            ]);
            redirect()->to('/dashboard');

        }
    }

    public function manualValidation(){
        if($this->checkAlert()){
            Toaster::error('Alert already exists in the database');
            return;
        }elseif($this->alertId == ''){
            Toaster::error('Alert ID is required!');
            return;
        }elseif($this->observation == ''){
            Toaster::error('Observation is required!');
            return;
        }elseif($this->alertStatus == ''){
            Toaster::error('Alert Status is required!');
            return;
        }elseif($this->detectionDate == ''){
            Toaster::error('Detection Date is required!');
            return;
        }elseif($this->region == 'Please select'){
            Toaster::error('Region is required!');
            return;
        }elseif($this->province == 'Please select'){
            Toaster::error('Province is required!');
            return;
        }elseif($this->alertStatus == 'rejected' && $this->alertNote == ''){
            Toaster::error('Alert note is required because you rejected this alert!');
            return;
        }
        return true;
    }
}
