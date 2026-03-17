<?php

namespace App\Http\Controllers;

use App\Models\House;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $totalHouses = House::available()->count();

        return view('home.index', compact('totalHouses'));
    }
}
