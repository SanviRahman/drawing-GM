<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\Website\HomeData;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(HomeData $data): View
    {
        return view('website.pages.home', $data->load());
    }
}
