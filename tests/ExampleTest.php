<?php

use Egmond\InertiaTables\Tests\Database\Models\User;

it('can test', function () {
    expect(true)->toBeTrue();
});

it('can create test models', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBeString();
    expect($user->email)->toBeString();
});

it('database is properly configured', function () {
    expect(config('database.default'))->toBe('testing');
    expect(config('database.connections.testing.driver'))->toBe('sqlite');
});
