<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PermissionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', 'App\Http\Controllers\UserController@index');
// Route::get('{slug}', 'App\Http\Controllers\UserController@index');

Route::any('{slug}', function () {
    return view('welcome');
});

Route::get('/', function () {
    return view('welcome');
});

Route::group(['middleware' => 'auth'], function () {
    // Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // Route::resource('user', 'App\Http\Controllers\UserController');

    // Route::get('init', 'App\Http\Controllers\UserController@init');
    // Route::post('login', 'App\Http\Controllers\UserController@login');
    // Route::post('logout', 'App\Http\Controllers\UserController@logout');

    // Route::get('/permission', [App\Http\Controllers\PermissionController::class, 'index'])->name('permission.index');
    // Route::get('/permission/create', [App\Http\Controllers\PermissionController::class, 'create'])->name('permission.create');
    // Route::post('', 'App\Http\Controllers\PermissionController@store')->name('permission.store');
    // Route::get('/permission/{id}/edit', 'App\Http\Controllers\PermissionController@edit')->name('permission.edit');

    // Route::get('/role', 'App\Http\Controllers\RoleController@index')->name('role.index');
    // Route::get('/role/create', 'App\Http\Controllers\RoleController@create')->name('role.create');

    // Route::get('/profile', [App\Http\Controllers\UserController::class, 'profile'])->name('user.profile');
    // Route::post('/profile', [App\Http\Controllers\UserController::class, 'postProfile'])->name('user.postProfile');
    // Route::get('/password/change', 'App\Http\Controllers\UserController@getPassword')->name('userGetPassword');
    // Route::post('/password/change', 'App\Http\Controllers\UserController@postPassword')->name('userPostPassword');

    // Route::get('/user', 'App\Http\Controllers\UserController@index')->name('user.index');
});

Route::prefix('/user')->group(function () {
    Route::get('/init', 'App\Http\Controllers\UserController@init');
    Route::post("/login", "App\Http\Controllers\UserController@login");
    Route::get("/login", "App\Http\Controllers\UserController@init");
    Route::get('/logout', 'App\Http\Controllers\UserController@logout');
});

Route::group(['middleware' => ['auth', 'role_or_permission:admin|create role|create permission']], function () {
    // Route::resource('permission', 'App\Http\Controllers\PermissionController');
    // Route::resource('role', 'App\Http\Controllers\RoleController');

    ////////////////////////// axios role
    Route::prefix('/role')->group(function () {
        Route::get("/getAll", "App\Http\Controllers\RoleController@getAll");
        Route::post("/getPermissionsByRoles", "App\Http\Controllers\RoleController@getPermissionsByRoles");
        Route::post('/create', "App\Http\Controllers\RoleController@store");
        Route::put('/update/{id}', 'App\Http\Controllers\RoleController@update');
        Route::delete('/delete/{id}', 'App\Http\Controllers\RoleController@delete');
        Route::post('/deleteLayer', 'App\Http\Controllers\RoleController@deleteLayer');
        Route::post('/assigmentLayers', 'App\Http\Controllers\RoleController@assigmentLayers');
        Route::post('/addAssigmentLayers', 'App\Http\Controllers\RoleController@addAssigmentLayers');
        Route::get('/getLayerPermissionsByRoleId/{id}', 'App\Http\Controllers\RoleController@getLayerPermissionsByRoleId');
    });

    ////////////////////////// axios request
    Route::prefix('/permission')->group(function () {
        Route::get('/getAll', 'App\Http\Controllers\PermissionController@getAll');
        Route::post('/create', 'App\Http\Controllers\PermissionController@store');
        Route::put('/update/{id}', 'App\Http\Controllers\PermissionController@update');
        Route::delete('/delete/{id}', 'App\Http\Controllers\PermissionController@delete');
    });

    //////////////////////axios user
    Route::prefix('/user')->group(function () {
        Route::post("/getJsPermissions", "App\Http\Controllers\UserController@getJsPermissions");
        Route::get("/getAll", "App\Http\Controllers\UserController@getAll");
        Route::delete('/delete/{id}', 'App\Http\Controllers\UserController@delete');
    });

    //////////////////////axios category
    // Route::resource('/categoryes', 'App\Http\Controllers\CategoryController');
    Route::prefix('/categories')->group(function () {
        Route::post("/create", "App\Http\Controllers\CategoryController@store");
        Route::get("/getAll", "App\Http\Controllers\CategoryController@index");
        Route::put('/update/{id}', 'App\Http\Controllers\CategoryController@update');
        Route::delete('/delete/{id}', 'App\Http\Controllers\CategoryController@delete');
    });

    //////////////////////axios subCategory
    Route::prefix('/subcategory')->group(function () {
        Route::post("/create", "App\Http\Controllers\SubCategoryController@store");
        Route::get("/getAll", "App\Http\Controllers\SubCategoryController@index");
        Route::put('/update/{id}', 'App\Http\Controllers\SubCategoryController@update');
        Route::delete('/delete/{id}', 'App\Http\Controllers\SubCategoryController@delete');
    });
});

// Route::group(['middleware' => ['auth', 'role_or_permission:admin|create role|create permission']], function () {
//     Route::resource('permission', 'App\Http\Controllers\PermissionController');
// });


Auth::routes(['register' => false]);






Route::get('/search/user', 'App\Http\Controllers\UserController@search');
Route::post('/account/create', 'App\Http\Controllers\UserController@store');
Route::put('/account/update/{id}', 'App\Http\Controllers\UserController@update');

//////////////////////axios layer
Route::prefix('/layer')->group(function () {
    Route::post('/create', 'App\Http\Controllers\LayerController@store');
    Route::put('/update/{id}', 'App\Http\Controllers\LayerController@update');
    Route::get('/get_layers', 'App\Http\Controllers\LayerController@getAll');
    Route::get('/getAll', 'App\Http\Controllers\LayerController@index');
    Route::delete('/delete/{id}', 'App\Http\Controllers\LayerController@delete');
});
