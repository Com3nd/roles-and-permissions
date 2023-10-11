<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use function Laravel\Prompts\password;
use function Pest\Laravel\delete;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $users = User::latest()->paginate(5);

        return view('users.index', compact(['users']))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $roles = Role::pluck('name', 'name');
        return view('users.create', compact(['roles']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:user.email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->assignRole($validated['roles']);

        return redirect()->route('users.index')
            ->with('success', 'User Created Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user):View
    {
        return view('users.show', compact(['user']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user):view
    {
        $roles = Role::pluck('name', 'name')->all();
        $userRoles = $user->roles->pluck('name', 'name')->all();

        return view('users.edit', compact(['roles', 'user', 'userRoles']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => [
                'required',
                'email',
                'unique:user.email'.$user->id
            ],
            'password' => 'same:confirm-password',
            'roles' => 'required',
        ]);

        if(!empty($request['password'])){
            $validated['password'] = Hash::make($validated['password']);
        }
        else {
            $validated = Arr::except($validated, array('password'));
        }
        $user->update($validated);

        DB::table('model_has_roles')->where('model_id', $user->id)->delete();
        $user->assignRole($validated['roles']);

        return redirect()->route('users.index')
            ->with('success', 'user Updated Successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user):RedirectResponse
    {
        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'User Deleted Successfully');
    }
}
