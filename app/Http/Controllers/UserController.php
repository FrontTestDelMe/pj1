<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Auth;


class UserController extends Controller
{

    public function init()
    {
        if (Auth::check()) {
            $user = Auth::user();
        } else {
            $user = false;
        }

        return response()->json($user, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('user.index');
    }

    public function getAll(Request $request)
    {

        if ($request->search) {
            $users = User::latest()
                ->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orderBy('created_at', 'desc')
                ->get();

            $users->transform(function ($user) {
                $user->roles = $user->getRoleNames()->first();
                $user->userPermissions = $user->getPermissionNames();
                $user->AllPermissions = User::findOrFail($user->id)->getAllPermissions(); //+-
                return $user;
            });
        } else {
            $users = User::latest()->get();
            $users->transform(function ($user) {
                $user->roles = $user->getRoleNames()->first();
                $user->userPermissions = $user->getPermissionNames();
                $user->AllPermissions = User::findOrFail($user->id)->getAllPermissions(); //+-
                return $user;
            });
        }



        // $user = User::findOrFail(1);
        // return dd($user->getAllPermissions());

        // return auth()->user()->getAllPermissions();
        return response()->json([
            'users' => $users
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

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'bail|required|email',
            'password' => 'bail|required|min:8'
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
            return response()->json(Auth::user(), 200);
        } else {
            return response()->json(['error' => 'Could not log you in.'], 401);
        }
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();

        return response()->json(['status' => 200]);
        // return redirect('login')->with(Auth::logout());
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
            'password' => 'required|alpha_num|min:6',
            // 'roles' => 'required',
            'email' => 'required|email|unique:users'
        ]);

        $user = User::where('name', $request->name)->first();

        if (isset($user->id)) {
            return response()->json(['error' => 'Username already exists.'], 401);
        }

        $user = new User();

        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);

        if ($request->has('roles')) {
            $user->assignRole($request->roles);
        }

        if ($request->has('permissions')) {
            $user->givePermissionTo($request->permissions);
        }

        $user->save();

        return response()->json("User Created", 200);
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
            'password' => 'nullable|alpha_num|min:6',
            // 'role' => 'required',
            'email' => 'required|email|unique:users,email,' . $id
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->has('roles')) {
            // $userRole = $user->getRoleNames();
            // foreach ($userRole as $role) {
            //     $user->removeRole($role);
            // }
            // $user->assignRole($request->roles);
            $user->syncRoles($request->roles);
        }

        if ($request->has('permissions')) {
            $userPermissions = $user->getPermissionNames();
            foreach ($userPermissions as $permission) {
                $user->revokePermissionTo($permission);
            }
            $user->givePermissionTo($request->permissions);
        }

        $user->save();

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

    public function profile()
    {
        return view("profile.index");
    }

    public function postProfile(Request $request)
    {
        $user = auth()->user();
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id
        ]);

        $user->update($request->all());
        return redirect()->back()->with('success', 'Профиль успешно изменен!');
    }

    public function getPassword()
    {
        return view('profile.password');
    }

    public function postPassword(Request $request)
    {
        $this->validate($request, [
            'newpassword' => 'required|min:6|max:30|confirmed'
        ]);

        $user = auth()->user();
        $user->update([
            'password' => bcrypt($request->newpassword)
        ]);

        return redirect()->back()->with('success', 'Password has been Changed');
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json('ok', 200);
    }

    public function search(Request $request)
    {
        $searchWord = $request->get('s');
        $users = User::where(function ($query) use ($searchWord) {
            $query->where('name', 'LIKE', "%$searchWord%")->orWhere('email', 'LIKE', "%$searchWord%");
        })->latest()->get();

        $users->transform(function ($user) {
            $user->role = $user->getRoleNames()->first();
            $user->userPermissions = $user->getPermissionNames();
            return $user;
        });

        return response()->json([
            'users' => $users
        ], 200);
    }

    public function getJsPermissions()
    {
        return response()->json([
            'csrfToken' => csrf_token(),
            'jsPermissions' => auth()->check() ? auth()->user()->jsPermissions() : null,
        ], 200);
    }
}
