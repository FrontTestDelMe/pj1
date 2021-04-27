<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Collection;
use App\Models\RolesLayers;
use App\Models\Layers;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(Role $role)
    {
        // $this->middleware("auth");
        $this->role = $role;
    }
    public function index()
    {
        $roles = $this->role::all();
        return view('role.index', ['roles' => $roles]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('role.create');
    }

    public function getAll()
    {
        $roles = $this->role->all();
        $roles->transform(function ($role) {
            $role->rolePermissions = $role->getPermissionNames();
            return $role;
        });

        return response()->json([
            'roles' => $roles
        ], 200);
    }

    public function getPermissionsByRoles(Request $request)
    {

        $this->validate($request, [
            'roles' => 'required',
        ]);

        $roles = $this->role->whereIn('name', $request->roles)->get();

        $permissions = [];

        for ($index = 0; $index < count($roles); $index++) {
            array_push($permissions, $roles[$index]->getPermissionNames());
        }

        $tmp_str = "";

        for ($index = 0; $index < count($permissions); $index++) {
            for ($i = 0; $i < count($permissions[$index]); $i++) {
                $tmp_str .= $permissions[$index][$i] . ";";
                $tmp_str .= $permissions[$index][$i] . ";";
            }
        }

        $tmp_str = substr($tmp_str, 0, -1);

        $permissions = explode(";", $tmp_str);

        $permissions = collect($permissions)->unique();
        return response()->json([
            'permissions' => $permissions
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
            'name' => 'required|string',
            'permission' => 'nullable'
        ]);

        $role = $this->role->create(
            [
                'name' => $request->name
            ]
        );

        if ($request->has("permissions")) {
            $role->givePermissionTo($request->permissions);
        }

        return response()->json("Role Created", 200);
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
            'name' => 'required|string',
            'permissions' => 'required',
        ]);

        $role = Role::findOrFail($id);
        $role->name = $request->name;

        if ($request->has('permissions')) {
            $rolePermissions = $role->getPermissionNames();
            foreach ($rolePermissions as $permission) {
                $role->revokePermissionTo($permission);
            }
            $role->givePermissionTo($request->permissions);
        }

        $role->save();

        return response()->json('ok', 200);
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
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

    public function deleteLayer(Request $request)
    {
        $this->validate($request, [
            'id_layer' => 'required',
            'id_role' => 'required',
        ]);

        $rolesLayers = new RolesLayers();
        $rolesLayers->where('id_role', '=', $request->id_role)->where('id_layer', '=', $request->id_layer)->delete();
        return response()->json("ok", 200);
    }

    public function assigmentLayers(Request $request)
    {
        $this->validate($request, [
            'layers' => 'required',
            'id_role' => 'required',
        ]);

        $layers = collect(json_decode($request->layers));

        $rolesLayers = new RolesLayers();
        $rolesLayers->where('id_role', '=', $request->id_role)->delete();

        for ($index = 0; $index < count($layers); $index++) {
            $layer = $layers[$index];

            $rolesLayers = new RolesLayers();

            $rolesLayers->id_layer = $layer->id;
            $rolesLayers->id_role = $request->id_role;

            $rolesLayers->permissions = '"read":' . $layer->read . ',"write":' . $layer->write . ',"update":' . $layer->update . ',"delete":' . $layer->delete . '';
            // $rolesLayers->updateOrInsert(['id_role' => $request->id_role, 'id_layer' => $layer->id,], ['id_layer' => $layer->id, 'permissions' => '"read":' . $layer->read . ',"write":' . $layer->write . ',"update":' . $layer->update . ',"delete":' . $layer->delete . '']);
            $rolesLayers->save();
        }

        // $layers = $layers->each(function ($item, $key) {
        //     //    $someid = $key;
        //     if ($key == "id") {
        //         $layers = $item;
        //     }
        // });

        // return $layers;



        return response()->json("ok", 200);
    }

    public function addAssigmentLayers(Request $request)
    {
        $this->validate($request, [
            'id_role' => 'required',
            'layers' => 'required',
        ]);

        $layers = collect(json_decode($request->layers));
        for ($index = 0; $index < count($layers); $index++) {
            $layer_id = $layers[$index];

            if (RolesLayers::where('id_role', '=', $request->id_role)->where('id_layer', '=', $layer_id)->doesntExist()) {
                $rolesLayers = new RolesLayers();

                $rolesLayers->id_layer = $layer_id;
                $rolesLayers->id_role = $request->id_role;
                $rolesLayers->permissions = '"read":0,"write":0,"update":0,"delete":0';
                $rolesLayers->save();
            }
        }
        return response()->json("ok", 200);
    }

    public function getLayerPermissionsByRoleId($id_role)
    {
        // $this->validate($request, [
        //     'id_role' => 'required',
        // ]);

        $layers = Role::with('getLayersRelation')
            ->where('id', '=', $id_role)
            ->get();

        // $rolesLayers = new RolesLayers;
        // $rolesLayers = $rolesLayers->where('id_role', '=', $id_role)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'rolesLayers' => $layers
        ], 200);
    }
}
