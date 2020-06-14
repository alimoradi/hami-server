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
    public function show($id)
    {
        //var_dump(ProviderCategory::get());
        return ProviderCategory::where('id', $id)->first();
        
    }
    public function add(Request $request)
    {
        $request->validate(
            ['name'=> 'required',
            'description'=>'required',
             'icon_name' => 'required']
        );
        $name = $request->input('name');
        $iconName = $request->input('icon_name');
        $category = new ProviderCategory();
        $category->name = $name;
        $category->description = $request->input('description');
        $category->icon_name = $iconName;
        $category->save();

        return ProviderCategory::find($category->id);
    }
    public function edit(Request $request)
    {
        $request->validate(
            [
            'id' => 'required',
            'name'=> 'required',
            'description'=>'required',
             'icon_name' => 'required']
        );
        $category = ProviderCategory::find($request->input('id'));
        $name = $request->input('name');
        $iconName = $request->input('icon_name');
        $category->name = $name;
        $category->description = $request->input('description');
        $category->icon_name = $iconName;
        $category->save();

        return $category;
    }
}
