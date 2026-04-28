<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use App\Services\LoginSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private array $roles = ['hotel_manager', 'cashier', 'chef', 'admin', 'user'];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roleRecord')->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create', ['roles' => Role::orderBy('display_name')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        $role = Role::findOrFail($request->role_id);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $role->name,
            'role_id' => $role->id,
            'password' => Hash::make($request->password),
        ]);
        AuditService::log('user.create', $user, $user->getAttributes());

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', ['user' => $user, 'roles' => Role::orderBy('display_name')->get()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone,'.$user->id],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);
        $role = Role::findOrFail($request->role_id);

        $original = $user->getOriginal();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role = $role->name;
        $user->role_id = $role->id;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $changes = [];
        foreach ($user->getChanges() as $key => $value) {
            $changes[$key] = ['from' => $original[$key] ?? null, 'to' => $value];
        }
        AuditService::log('user.update', $user, $changes);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->withErrors(['role' => 'You cannot delete your own account.'])->withInput();
        }
        $user->delete();
        AuditService::log('user.delete', $user, ['deleted' => true]);
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Manually remove login lock and counters.
     */
    public function unlockLoginLock(User $user, LoginSecurityService $loginSecurityService)
    {
        $loginSecurityService->unlockUser($user);

        return redirect()->route('users.index')->with('success', 'User login lock cleared successfully.');
    }
}
