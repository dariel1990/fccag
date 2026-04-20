<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserPermissionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class UserController extends Controller
{
    public function __construct(private readonly UserPermissionService $permissionService) {}

    public function index(): Response
    {
        return Inertia::render('users/Index', [
            'users' => User::query()
                ->select(['id', 'name', 'email', 'is_superadmin', 'permissions', 'created_at'])
                ->orderBy('name')
                ->get(),
            'modules' => array_map(fn (\App\Enums\Module $m) => ['name' => $m->name, 'value' => $m->value], \App\Enums\Module::cases()),
            'actions' => array_map(fn (\App\Enums\PermissionAction $a) => ['name' => $a->name, 'value' => $a->value], \App\Enums\PermissionAction::cases()),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'is_superadmin' => $request->boolean('is_superadmin'),
        ]);

        if ($request->boolean('full_access')) {
            $this->permissionService->grantFullAccess($user);
        } elseif ($request->filled('permissions')) {
            $this->permissionService->sync($user, $request->validated('permissions'));
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'is_superadmin' => $request->boolean('is_superadmin'),
        ]);

        if ($request->boolean('full_access')) {
            $this->permissionService->grantFullAccess($user);
        } else {
            $this->permissionService->sync($user, $request->validated('permissions') ?? []);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 403, 'You cannot delete your own account.');

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
