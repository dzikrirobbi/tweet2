<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Article;
use App\Http\Requests ;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class TweetController extends Controller
{
    public function index()
    {
        return Tweet::all();
    }

    public function byDate() {
        $start  = Input::get('start') ;
        $end = Input::get('end') ;
        //return Redirect::to('people/'.$lastName.'/'.$firstName) ;
	return  Tweet::whereRaw("(created_at >= ? AND created_at <= ?)", [$start." 00:00:00", $end." 23:59:59"])->get();
    }

}
