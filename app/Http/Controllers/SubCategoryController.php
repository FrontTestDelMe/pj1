<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use App\Models\CategorySubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subCategory = SubCategory::with('getCategoriesRelation')->get();

        return response()->json([
            'subCategory' => $subCategory
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'priority' => 'required',
            'category' => 'required',
        ]);

        $subCategories = new SubCategory();
        $categorySubCategory = new CategorySubCategory();

        $subCategories->name = $request->name;
        $subCategories->priority = $request->priority;

        $subCategories->save();

        $categorySubCategory->cat_id = $request->category;
        $categorySubCategory->sub_cat_id = $subCategories->id;

        $categorySubCategory->save();

        return response()->json("SubCategory created", 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'priority' => 'required',
            'category' => 'required',
        ]);

        $subCategories = SubCategory::findOrFail($id);
        $subCategories->name = $request->name;
        $subCategories->priority = $request->priority;

        $subCategories->save();

        $categorySubCategory = CategorySubCategory::where('sub_cat_id', $id)->first();
        $categorySubCategory->delete();

        $categorySubCategory->cat_id = $request->category;
        $categorySubCategory->sub_cat_id = $subCategories->id;

        $categorySubCategory->save();

        return response()->json('ok', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function delete($id)
    {
        $subCategory = SubCategory::findOrFail($id);
        $subCategory->delete();

        $categorySubCategory = CategorySubCategory::where('sub_cat_id', $id)->first();
        $categorySubCategory->delete();

        return response()->json('ok', 200);
    }
}
