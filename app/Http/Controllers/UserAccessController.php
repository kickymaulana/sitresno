<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserAccessRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserAccessController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Users/Index', [
            'users' => User::query()
                ->with('roles:id,name')
                ->orderByDesc('is_approved')
                ->orderBy('name')
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'nik' => $user->nik,
                    'name' => $user->name,
                    'email' => $user->email,
                    'isApproved' => $user->is_approved,
                    'role' => $user->roles->first()?->name,
                    'lastLoginAt' => $user->last_login_at?->toIso8601String(),
                ]),
            'roles' => ['admin', 'inspector', 'supervisor', 'manager'],
        ]);
    }

    public function update(UpdateUserAccessRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $isApproved = (bool) $validated['is_approved'];

        $user->forceFill([
            'is_approved' => $isApproved,
            'approved_at' => $isApproved ? ($user->approved_at ?? now()) : null,
        ])->save();
        $user->syncRoles([$validated['role']]);

        return back()->with('status', 'Akses pengguna berhasil diperbarui.');
    }
}
