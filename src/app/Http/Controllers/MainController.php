<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MainController extends Controller
{
    public function index()
    {
        return view('items.index');
    }

    public function brothers()
    {
        return view('items.brothers');
    }

    public function eldestSon()
    {
        return view('items.eldest-son');
    }

    public function secondSon()
    {
        return view('items.second-son');
    }

    public function thirdSon()
    {
        return view('items.third-son');
    }

    public function fourthSon()
    {
        return view('items.fourth-son');
    }
}
