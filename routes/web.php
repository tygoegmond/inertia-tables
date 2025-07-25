<?php

use Egmond\InertiaTables\Http\Controllers\ActionController;
use Illuminate\Support\Facades\Route;

Route::post('/inertia-tables/action', ActionController::class)
    ->name('inertia-tables.action')
    ->middleware(['web', 'signed']);
