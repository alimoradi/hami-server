<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ProviderCategory;
use Illuminate\Http\Request;

class ProviderCategoriesController extends Controller
{
    public function index()
    {
        //var_dump(ProviderCategory::get());
        return json_encode(ProviderCategory::get(), JSON_UNESCAPED_UNICODE);
    }
}
