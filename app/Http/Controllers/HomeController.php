<?php

namespace App\Http\Controllers;

use App\Util\ArtisanInBackground;
use Log;

class HomeController extends Controller
{
    public function index()
    {
        return redirect(route('home'));
    }

    public function home()
    {
        return view('home');
    }

    public function sync()
    {
        Log::info('Started manual library sync from web.');
        ArtisanInBackground::run('sync:library');
        return redirect(route('home'));
    }
}
