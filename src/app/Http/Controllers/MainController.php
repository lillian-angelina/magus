<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Song;
use App\Http\Controllers\Controller;


class MainController extends Controller
{
    public function index()
    {
        // DBから全件取得
        $songs = \App\Models\Song::all();
        return view('items.index', compact('songs'));
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

    public function contact()
    {
        return view('items.contact');
    }

    public function tabIndex()
    {
        return view('items.tab-index');
    }
}
