<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    public function getCategoriesRelation()
    {
        return $this->belongsToMany('App\Models\Categories', 'category_sub_categories', 'sub_cat_id', 'cat_id');
    }
}
