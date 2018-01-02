<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class HomeController
 * @package App\Http\Controllers\API\V1
 */
class HomeController extends Controller
{
    public function getIndex()
    {
        dd('home');
    }
}
