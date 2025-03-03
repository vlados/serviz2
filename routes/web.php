<?php

use App\Http\Controllers\ServiceOrderLabelController;
use Illuminate\Support\Facades\Route;

// Redirect root URL to admin panel
Route::redirect('/', '/admin');

// Service order label printing routes
Route::get('/service-orders/{serviceOrder}/print-label', [ServiceOrderLabelController::class, 'printLabel'])
    ->name('service-orders.print-label');
Route::get('/service-orders/print-bulk-labels/{batchId}', [ServiceOrderLabelController::class, 'printBulkLabels'])
    ->name('service-orders.print-bulk-labels');