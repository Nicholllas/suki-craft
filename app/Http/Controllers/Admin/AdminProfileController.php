<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPasswordUpdateRequest;
use App\Http\Requests\Admin\AdminProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', ['admin' => $request->user('admin')]);
    }

    public function update(AdminProfileUpdateRequest $request): RedirectResponse
    {
        $request->user('admin')->update($request->validated());

        return back()->with('success', 'Profil admin berhasil diperbarui.');
    }

    public function updatePassword(AdminPasswordUpdateRequest $request): RedirectResponse
    {
        $request->user('admin')->update(['password' => $request->validated('password')]);

        return back()->with('success', 'Password admin berhasil diperbarui.');
    }
}
