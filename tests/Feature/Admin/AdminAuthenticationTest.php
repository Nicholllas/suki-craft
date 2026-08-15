<?php

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeAdmin(array $attributes = []): Admin
{
    return Admin::create([
        'email' => 'admin'.uniqid().'@sukicraft.test',
        'is_active' => true,
        'name' => 'Admin Test',
        'password' => 'password',
        'role' => AdminRole::SUPER_ADMIN,
        ...$attributes,
    ]);
}

test('guests are redirected to the dedicated admin login page', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
});

test('authenticated admin is redirected from the admin login page to the dashboard', function () {
    $admin = makeAdmin();

    $this->actingAs($admin, 'admin')->get(route('admin.login'))->assertRedirect(route('admin.dashboard'));
});

test('active admin can authenticate without logging in as a customer', function () {
    $admin = makeAdmin();

    $response = $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password']);

    $response->assertRedirect(route('admin.dashboard', absolute: false));
    $this->assertAuthenticatedAs($admin, 'admin');
    $this->assertGuest('web');
});

test('admin sign-in ignores storefront intended URLs', function () {
    $admin = makeAdmin();

    $this->withSession(['url.intended' => route('home')])
        ->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard', absolute: false));
});

test('inactive admin cannot authenticate', function () {
    $admin = makeAdmin(['is_active' => false]);

    $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest('admin');
});

test('staff cannot access admin account management', function () {
    $staff = makeAdmin(['role' => AdminRole::STAFF]);

    $this->actingAs($staff, 'admin')->get(route('admin.accounts.index'))->assertForbidden();
});
