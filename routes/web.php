<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryAlertController;
use App\Http\Controllers\AccountSwitcherController;

// Fallback / index default target route
Route::get('/', [InventoryAlertController::class, 'index'])->name('alerts.index');

// Submodule data and mutation API endpoints
Route::get('/alerts-reorders/data', [InventoryAlertController::class, 'getData'])->name('alerts.data');
Route::post('/alerts-reorders/update-limits/{id}', [InventoryAlertController::class, 'updateLimits']);
Route::post('/alerts-reorders/submit-po', [InventoryAlertController::class, 'submitPO']);
Route::post('/alerts-reorders/pipeline/{id}', [InventoryAlertController::class, 'processPipeline']);
Route::post('/alerts-reorders/pipeline/{id}/receive', [InventoryAlertController::class, 'markReceived'])->name('pipeline.receive');

// NEW: session-based account switcher, no password login required
Route::post('/account/switch', [AccountSwitcherController::class, 'switch'])->name('account.switch');

// NEW: stock alert actions
Route::post('/alerts-reorders/alerts/{alert}/acknowledge', [InventoryAlertController::class, 'acknowledgeAlert'])->name('alerts.acknowledge');
Route::post('/alerts-reorders/alerts/{alert}/resolve', [InventoryAlertController::class, 'resolveAlert'])->name('alerts.resolve');

// Auto-reorder — per-item toggle and draft review actions
Route::post('/alerts-reorders/auto-reorder/{id}', [InventoryAlertController::class, 'toggleAutoReorder']);
Route::post('/alerts-reorders/draft/{id}/submit', [InventoryAlertController::class, 'submitDraft']);
Route::post('/alerts-reorders/draft/{id}/discard', [InventoryAlertController::class, 'discardDraft']);
