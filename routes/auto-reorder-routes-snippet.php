<?php
/**
 * routes/web.php wasn't included in the uploaded zip, so add these
 * inside your existing `alerts-reorders` route group (alongside
 * submit-po, update-limits, pipeline, etc).
 */

use App\Http\Controllers\InventoryAlertController;

Route::post('/alerts-reorders/auto-reorder/{id}', [InventoryAlertController::class, 'toggleAutoReorder']);
Route::post('/alerts-reorders/draft/{id}/submit', [InventoryAlertController::class, 'submitDraft']);
Route::post('/alerts-reorders/draft/{id}/discard', [InventoryAlertController::class, 'discardDraft']);
