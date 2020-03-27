<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Provider;
use App\ProviderCategory;
use Illuminate\Http\Request;


class ProvidersController extends Controller
{
    public function getByCategoryId($categoryId)
    {
        
        return Provider::with(['user', 'providerCategory'])->where('provider_category_id', $categoryId)->get();
    }
    public function getByUid($uid)
    {
        return Provider::with(['user'])->whereHas('user', function ($query) use($uid) {
            $query->where('tinode_uid', '=', $uid);
        })->first();
    }
    public function getById($id)
    {
        return Provider::with(['user'])->where('id', $id)->first();
    }
}
