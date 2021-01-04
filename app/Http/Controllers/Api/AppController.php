<?php

namespace App\Http\Controllers\Api;

use App\AppVersion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppController extends Controller
{
    function checkForUpdates()
    {
        return AppVersion::orderBy('created_at', 'desc')->first();

    }
}
