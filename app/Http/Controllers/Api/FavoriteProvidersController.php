<?php

namespace App\Http\Controllers\Api;

use App\FavoriteProvider;
use App\Http\Controllers\Controller;
use ErrorException;
use Illuminate\Http\Request;

class FavoriteProvidersController extends Controller
{
    public function add($providerId)
    {
        
        $favoriteProvider = FavoriteProvider::where('user_id', auth()->user()->id)->where('provider_id', $providerId)->first();
        if(!$favoriteProvider) {
           
            $favoriteProvider = new FavoriteProvider();
            $favoriteProvider->user_id = auth()->user()->id;
            $favoriteProvider->provider_id = $providerId;
            $favoriteProvider->save();
        }
        
        
        return FavoriteProvider::with(['provider','provider.user', 'provider.providerCategory'])->find($favoriteProvider->id);
    }
    public function delete($providerId)
    {
        FavoriteProvider::where('user_id', auth()->user()->id)->where('provider_id', $providerId)->delete();
        return response()->json(['success' => true]);
    }
    public function index()
    {
        return FavoriteProvider::with(['provider','provider.user', 'provider.providerCategories'])->where('user_id', auth()->user()->id)->get();
    }
}
