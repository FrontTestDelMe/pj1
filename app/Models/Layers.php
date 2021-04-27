<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layers extends Model
{
    use HasFactory;

    public function getSubCategoriesRelation()
    {
        return $this->belongsToMany('App\Models\SubCategory', 'sub_category_layers', 'layer_id', 'sub_cat_id');
    }

    public function getRolesRelation()
    {
        return $this->belongsToMany('Spatie\Permission\Models\Role', 'roles_layers', 'id_layer', 'id_role');
    }
}
