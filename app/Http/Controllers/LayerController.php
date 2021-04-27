<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Layers;
use App\Models\SubCategoryLayer;

class LayerController extends Controller
{

    public function getAll()
    {
        $layers = Layers::with('getSubCategoriesRelation.getCategoriesRelation')->orderBy('created_at', 'desc')->get(); //Layers::latest()->get();

        return response()->json([
            'layers' => $layers
        ], 200);
    }

    public function index()
    {
        $layers = Layers::orderBy('created_at', 'desc')->get();

        return response()->json([
            'layers' => $layers
        ], 200);
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
            'link' => 'required',
            'description' => 'required',
            'priority' => 'required',
            'selected_sub_category' => 'required',
        ]);

        $layer = new Layers();
        $subCategoryLayer = new SubCategoryLayer();

        $layer->name = $request->name;
        $layer->link = $request->link;
        $layer->description = $request->description;
        $layer->priority = $request->priority;

        $layer->save();

        $subCategoryLayer->sub_cat_id = $request->selected_sub_category;
        $subCategoryLayer->layer_id = $layer->id;

        $subCategoryLayer->save();

        return response()->json("Layer Created", 200);
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
            'link' => 'required',
            'description' => 'required',
            'priority' => 'required',
            'selected_sub_category' => 'required'
        ]);

        $layer = Layers::findOrFail($id);
        $layer->name = $request->name;
        $layer->link = $request->link;
        $layer->description = $request->description;
        $layer->priority = $request->priority;

        $layer->save();

        $subCategoryLayer = SubCategoryLayer::where('layer_id', $id)->first();
        $subCategoryLayer->sub_cat_id = $request->selected_sub_category;
        $subCategoryLayer->save();

        return response()->json('ok', 200);
    }

    public function delete($id)
    {
        $layer = Layers::findOrFail($id);
        $layer->delete();

        $subCategoryLayer = SubCategoryLayer::where('layer_id', $id)->first();
        $subCategoryLayer->delete();

        return response()->json('ok', 200);
    }
}
