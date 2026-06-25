<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


use Livewire\Component;
use Masmerise\Toaster\Toaster;

class EditAlertComponent extends Component
{
    public $alertId, $alertStatus, $detectionDate, $observation, $alertNote, $auditorStatus;
    public $chooseRegion = '', $chooseProvince = '';
    public $region = 'Please select', $province = 'Please select', $idAlert, $platformStatus ;

    public function getData(){
        return  DB::table('alerts')->where('alertId', $this->idAlert)->where('isActive', 1)->first();
    }
    public function mount($id){
        $this->idAlert = $id;
        $this->alertId = $this->getData()->alertId;
        $this->alertStatus = $this->getData()->alertStatus;
        $this->detectionDate = $this->getData()->detectionDate;
        $this->observation = $this->getData()->observation;
        $this->alertNote = $this->getData()->alertNote;
        $this->region = $this->getData()->region;
        $this->province = $this->getData()->province;
        $this->platformStatus = $this->getData()->platformStatus;
        $this->auditorStatus = $this->getData()->auditorStatus;
    }
    public function getRegions(){
        try {
            $req = Http::get('http://129.150.48.143:8080/geoserver/simontini/wfs',
            [
                'service' => 'wfs',
                'version' => '1.1.0',
                'request' => 'GetFeature',
                'typename' => 'simontini:province',
                'propertyName' => 'island_en',
                'cql_filter' => "island_en ILIKE '%". $this->chooseRegion ."%'",
                'maxFeatures' => 10,
                'outputFormat' => 'application/json',
            ]);
            $response = json_decode($req, true);
            // $arrUnique = array_unique($response['features'][0]['properties']['provinsi']);
            $res = array();
            foreach ($response['features'] as $each) {
                if (isset($res[$each['properties']['island_en']]))
                    array_push($res[$each['properties']['island_en']], $each['properties']['island_en']);
                else
                    $res[$each['properties']['island_en']] = array($each['properties']['island_en']);
                }
            return array_slice($res, 0, 10);
        } catch (\Throwable $th) {
            return [];
        }
    }

    public function getProvinces(){
        try {
            $req = Http::get('http://129.150.48.143:8080/geoserver/simontini/wfs',
            [
                'service' => 'wfs',
                'version' => '1.1.0',
                'request' => 'GetFeature',
                'typename' => 'simontini:province',
                'propertyName' => 'island_en,prov_en',
                'cql_filter' => "island_en = '". $this->region ."' AND prov_en ILIKE '%". $this->chooseProvince ."%'",
                'maxFeatures' => 10,
                'outputFormat' => 'application/json',
            ]);
            $response = json_decode($req, true);
            // $arrUnique = array_unique($response['features'][0]['properties']['provinsi']);
            $res = array();
            foreach ($response['features'] as $each) {
                if (isset($res[$each['properties']['prov_en']]))
                    array_push($res[$each['properties']['prov_en']], $each['properties']['prov_en']);
                else
                    $res[$each['properties']['prov_en']] = array($each['properties']['prov_en']);
                }
            return array_slice($res, 0, 10);
        } catch (\Throwable $th) {
            return [];
        }



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
        return view('livewire.edit-alert-component', compact('regions', 'provincies'));
    }


    public function checkAlertStatus(){
        $status = $this->auditorStatus;

        if($this->alertStatus == 'rejected'){
            $status = 'rejected';
        }

        return $status;
    }

    public function storeAlert(){
        // ponytail: terminal states are not editable — mirrors AlertController::editalert gate
        $alert = $this->getData();
        if (! $alert || in_array($alert->auditorStatus, ['approved', 'rejected', 'duplicate'])) {
            Toaster::error('This alert can no longer be edited');
            return redirect()->to('/alerts');
        }

        if($this->manualValidation()){
            DB::table('alerts')
            ->where('alertId', $this->idAlert)
            ->where('isActive', 1)
            ->update([
                'observation' => $this->observation,
                'alertStatus' => $this->alertStatus,
                'detectionDate' => $this->detectionDate,
                'alertNote' => $this->alertNote,
                'region' => $this->region,
                'province' => $this->province,
                'auditorStatus' => $this->checkAlertStatus(),
                'platformStatus' => $this->platformStatus,
                'updated_at' => Carbon::now('Asia/Jakarta')
            ]);
            if($this->alertStatus == 'rejected'){
                DB::table('auditorlog')->insert([
                    'auditorId' => session('id'),
                    'alertId' => $this->idAlert,
                    'ngapain' => 'Reject',
                    'created_at' => Carbon::now('Asia/Jakarta')
                ]);
            }
            redirect()->to('/alerts');
        }
    }

    public function manualValidation(){
        if($this->alertId == ''){
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
