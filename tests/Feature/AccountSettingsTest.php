<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('guests must sign in to access account settings', function () {
    $this->get(route('account.settings'))->assertRedirectToRoute('login');
    $this->put(route('account.password.update'))->assertRedirectToRoute('login');
});

test('signed-in users can see their own account settings', function (string $role) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)->get(route('account.settings'))
        ->assertOk()
        ->assertSee('Pengaturan Akun')
        ->assertSee($user->name)
        ->assertSee($user->email)
        ->assertSeeText(ucfirst($role))
        ->assertSee(route('logout'));
})->with(['admin', 'user']);

test('the current password and matching confirmation are required to change the password', function () {
    $user = User::factory()->create();
    $originalHash = $user->password;

    $this->actingAs($user)->from(route('account.settings'))
        ->put(route('account.password.update'), [
            'current_password' => 'incorrect-password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ])
        ->assertRedirectToRoute('account.settings')
        ->assertSessionHasErrors(['current_password', 'password']);

    expect($user->refresh()->password)->toBe($originalHash);
});

test('a signed-in user can change their password and log out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('account.password.update'), [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirectToRoute('account.settings')->assertSessionHas('success');

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();

    $this->post(route('logout'))->assertRedirectToRoute('login');
    $this->assertGuest();
});
