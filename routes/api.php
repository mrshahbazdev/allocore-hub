<?php

use App\Http\Controllers\Api\KpiIngestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Allocore Integration API
|--------------------------------------------------------------------------
|
| Inbound endpoints called by Allocore spoke tools (AuditPro, InvoiceMaker,
| EasySOP, ...). Authenticated with a per-company, per-tool key in the
| "X-Allocore-Api-Key" header (see App\Http\Middleware\AllocoreAuth).
|
*/
Route::prefix('allocore')->middleware('allocore')->group(function () {
    Route::post('/kpi/ingest', [KpiIngestController::class, 'ingest']);
});
