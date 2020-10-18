<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Amritms\WaveappsClientPhp\Waveapps;

class WebappsController extends Controller
{
    public function handleToken() {
        $waveapp = new Waveapps();
        $countries = $waveapp->countries();

        dd($countries);
    }
}
