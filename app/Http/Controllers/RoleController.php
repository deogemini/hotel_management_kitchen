<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('display_name')->get();
        $permissions = Permission::orderBy('module')->orderBy('display_name')->get()->groupBy('module');

        return view('roles.index', compact('roles', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? null,
        ]);
        $role->permissions()->sync($data['permissions'] ?? []);

        AuditService::log('role.update', $role, [
            'display_name' => $role->display_name,
            'permissions' => $role->permissions()->pluck('name')->all(),
        ]);

        return redirect()->route('roles.index')->with('success', 'Role permissions updated successfully.');
    }
}
