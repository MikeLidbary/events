<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Event;
class EventController extends Controller
{
    public function test(){
        $event = new Event();
        dd($event->fetchEvents("Nairobi","this_week"));
    }
}
