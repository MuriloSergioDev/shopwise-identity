<?php

declare(strict_types=1);

use App\Http\Controllers\IssueTokenController;
use App\Http\Controllers\RefreshTokenController;
use App\Http\Controllers\RegisterCustomerController;
use App\Http\Controllers\RevokeTokenController;
use App\Http\Controllers\ShowCurrentCustomerController;
use Illuminate\Support\Facades\Route;

// Called only by the BFFs. Contract: shopwise-contracts/openapi/identity.yaml. JWKS lives in bootstrap/app.php.
Route::middleware('throttle:credentials')->group(function (): void {
    Route::post('/customers', RegisterCustomerController::class)->name('customers.store');
    Route::post('/tokens', IssueTokenController::class)->name('tokens.store');
});

Route::post('/tokens/refresh', RefreshTokenController::class)->name('tokens.refresh');
Route::post('/tokens/revoke', RevokeTokenController::class)->name('tokens.revoke');

Route::middleware('auth.jwt')->get('/me', ShowCurrentCustomerController::class)->name('me');
