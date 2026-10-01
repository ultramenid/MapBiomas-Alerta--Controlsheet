<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(){
        $title = 'Settings - Mapbiomas Indonesia';
        $nav = 'settings';
        return view('settings', compact('title', 'nav'));
    }
}
