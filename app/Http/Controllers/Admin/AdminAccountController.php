<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminForcePasswordRequest;
use App\Http\Requests\Admin\AdminStoreRequest;
use App\Http\Requests\Admin\AdminUpdateRequest;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function index(): View
    {
        return view('admin.accounts.index', ['admins' => Admin::query()->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.accounts.create', ['roles' => AdminRole::cases()]);
    }

    public function store(AdminStoreRequest $request): RedirectResponse
    {
        Admin::create($request->validated());

        return redirect()->route('admin.accounts.index')->with('success', 'Akun admin berhasil ditambahkan.');
    }

    public function edit(Admin $admin): View
    {
        return view('admin.accounts.edit', ['admin' => $admin, 'roles' => AdminRole::cases()]);
    }

    public function update(AdminUpdateRequest $request, Admin $admin): RedirectResponse
    {
        $admin->update($request->validated());

        return redirect()->route('admin.accounts.index')->with('success', 'Akun admin berhasil diperbarui.');
    }

    public function deactivate(Request $request, Admin $admin): RedirectResponse
    {
        if ($request->user('admin')->is($admin)) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $admin->update(['is_active' => false]);

        return back()->with('success', 'Akun admin berhasil dinonaktifkan.');
    }

    public function resetPassword(AdminForcePasswordRequest $request, Admin $admin): RedirectResponse
    {
        $admin->update(['password' => $request->validated('password')]);

        return redirect()->route('admin.accounts.edit', $admin)->with('success', 'Password akun admin berhasil direset.');
    }
}
