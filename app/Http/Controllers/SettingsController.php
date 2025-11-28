<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller {
  public function toggleProduction(Request $r){
    $r->validate(['production'=>'required|boolean']);
    config(['payments.mp.environment'=> $r->boolean('production') ? 'production' : 'sandbox']);
    cache()->forever('payments.mp.environment', config('payments.mp.environment'));
    return response()->json(['ok'=>true]);
  }
}
