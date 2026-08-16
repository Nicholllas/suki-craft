<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerPasswordUpdateRequest;
use App\Http\Requests\Customer\CustomerProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('customer.profile.edit', ['customer' => $request->user('customer')]);
    }

    public function update(CustomerProfileUpdateRequest $request): RedirectResponse
    {
        $request->user('customer')->update($request->validated());

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(CustomerPasswordUpdateRequest $request): RedirectResponse
    {
        $request->user('customer')->update(['password' => $request->validated('password')]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
