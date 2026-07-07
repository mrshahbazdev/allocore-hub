<?php

use App\Http\Controllers\KpiDefinitionController;
use App\Http\Controllers\KpiSpreadsheetController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/kpis/spreadsheet');
});

// Locale Switcher
Route::get('/locale/{locale}', LocaleController::class)->name('locale');

// KPI Definitions
Route::get('/kpis', [KpiDefinitionController::class, 'index'])->name('kpis.index');
Route::get('/kpis/create', [KpiDefinitionController::class, 'create'])->name('kpis.create');
Route::post('/kpis', [KpiDefinitionController::class, 'store'])->name('kpis.store');
Route::get('/kpis/catalog', [KpiDefinitionController::class, 'catalog'])->name('kpis.catalog');
Route::post('/kpis/catalog/{template}/use', [KpiDefinitionController::class, 'useTemplate'])->name('kpis.use-template');
Route::get('/kpis/spreadsheet', [KpiSpreadsheetController::class, 'index'])->name('kpis.spreadsheet');
Route::get('/kpis/{kpi}', [KpiDefinitionController::class, 'show'])->name('kpis.show');
Route::get('/kpis/{kpi}/edit', [KpiDefinitionController::class, 'edit'])->name('kpis.edit');
Route::put('/kpis/{kpi}', [KpiDefinitionController::class, 'update'])->name('kpis.update');
Route::delete('/kpis/{kpi}', [KpiDefinitionController::class, 'destroy'])->name('kpis.destroy');

// KPI Spreadsheet (Excel-style monthly view)
Route::post('/kpis/spreadsheet/targets', [KpiSpreadsheetController::class, 'storeTargets'])->name('kpis.spreadsheet.targets');
Route::post('/kpis/spreadsheet/actuals', [KpiSpreadsheetController::class, 'storeActuals'])->name('kpis.spreadsheet.actuals');
Route::post('/kpis/spreadsheet/generate-targets', [KpiSpreadsheetController::class, 'generateTargets'])->name('kpis.spreadsheet.generate-targets');
