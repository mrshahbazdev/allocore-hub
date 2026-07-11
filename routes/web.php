<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\GmbhAnalyseController;
use App\Http\Controllers\ImmobilienController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JahresabschlussController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Invoice\Create as InvoiceCreate;
use App\Livewire\Invoice\Edit as InvoiceEdit;
use App\Livewire\Invoice\Index as InvoiceIndex;
use App\Livewire\Invoice\Show as InvoiceShow;
use App\Models\Analysis;
use Illuminate\Support\Facades\Route;

// ─── Public ────────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// ─── Authenticated Routes ───────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'company'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Companies
    Route::resource('companies', CompanyController::class);
    Route::post('/companies/{company}/switch', [CompanyController::class, 'switch'])->name('companies.switch');

    // GmbH Analyse
    Route::middleware('tool.access:gmbh')->group(function () {
        Route::get('/gmbh', [GmbhAnalyseController::class, 'index'])->name('gmbh.index');
        Route::get('/gmbh/create', [GmbhAnalyseController::class, 'create'])->name('gmbh.create');
        Route::post('/gmbh', [GmbhAnalyseController::class, 'store'])->name('gmbh.store');
        Route::get('/gmbh/{gmbh}', [GmbhAnalyseController::class, 'show'])->name('gmbh.show');
        Route::get('/gmbh/{gmbh}/edit', [GmbhAnalyseController::class, 'edit'])->name('gmbh.edit');
        Route::patch('/gmbh/{gmbh}', [GmbhAnalyseController::class, 'update'])->name('gmbh.update');
        Route::delete('/gmbh/{gmbh}', [GmbhAnalyseController::class, 'destroy'])->name('gmbh.destroy');
        Route::get('/gmbh/{gmbh}/pdf', [GmbhAnalyseController::class, 'exportPdf'])->name('gmbh.pdf');
    });

    // Jahresabschluss
    Route::middleware('tool.access:jahresabschluss')->group(function () {
        Route::get('/jahresabschluss', [JahresabschlussController::class, 'index'])->name('jahresabschluss.index');
        Route::get('/jahresabschluss/create', [JahresabschlussController::class, 'create'])->name('jahresabschluss.create');
        Route::post('/jahresabschluss', [JahresabschlussController::class, 'store'])->name('jahresabschluss.store');
        Route::get('/jahresabschluss/{jahresabschluss}', [JahresabschlussController::class, 'show'])->name('jahresabschluss.show');
        Route::delete('/jahresabschluss/{jahresabschluss}', [JahresabschlussController::class, 'destroy'])->name('jahresabschluss.destroy');
        Route::get('/jahresabschluss/{jahresabschluss}/pdf', [JahresabschlussController::class, 'exportPdf'])->name('jahresabschluss.pdf');
    });

    // Immobilienanalyse
    Route::middleware('tool.access:immobilien')->group(function () {
        Route::get('/immobilien', [ImmobilienController::class, 'index'])->name('immobilien.index');
        Route::get('/immobilien/create', [ImmobilienController::class, 'create'])->name('immobilien.create');
        Route::post('/immobilien', [ImmobilienController::class, 'store'])->name('immobilien.store');
        Route::get('/immobilien/compare', [ImmobilienController::class, 'compare'])->name('immobilien.compare');
        Route::get('/immobilien/{immobilien}', [ImmobilienController::class, 'show'])->name('immobilien.show');
        Route::delete('/immobilien/{immobilien}', [ImmobilienController::class, 'destroy'])->name('immobilien.destroy');
        Route::get('/immobilien/{immobilien}/pdf', [ImmobilienController::class, 'exportPdf'])->name('immobilien.pdf');
    });

    // Analyses History (all tools)
    Route::get('/analyses', function () {
        $company = auth()->user()?->currentCompany();

        $analyses = Analysis::with('company')
            ->when($company, fn ($query) => $query->where('company_id', $company->id))
            ->latest()
            ->paginate(20);

        return view('analyses.index', compact('analyses'));
    })->name('analyses.index');

    // Excel Import
    Route::get('/import', [ExcelImportController::class, 'show'])->name('import.index');
    Route::post('/import', [ExcelImportController::class, 'import'])->name('import.upload');
    Route::get('/import/template/{type}', [ExcelImportController::class, 'downloadTemplate'])->name('import.template');

    // Leads
    Route::resource('leads', LeadController::class);
    Route::post('/leads-transfer', [LeadController::class, 'transferToLeadOs'])->name('leads.transfer');
    Route::get('/leads-export', [LeadController::class, 'exportCsv'])->name('leads.export');

    // PayPal
    Route::get('/paypal', [PaypalController::class, 'index'])->name('paypal.index');
    Route::get('/paypal/settings', [PaypalController::class, 'settings'])->name('paypal.settings');
    Route::post('/paypal/settings', [PaypalController::class, 'saveSettings'])->name('paypal.save-settings');
    Route::post('/paypal/create-payment', [PaypalController::class, 'createPayment'])->name('paypal.create-payment');
    Route::get('/paypal/capture', [PaypalController::class, 'capture'])->name('paypal.capture');
    Route::get('/paypal/cancel', [PaypalController::class, 'cancel'])->name('paypal.cancel');
    Route::get('/paypal/{transaction}', [PaypalController::class, 'show'])->name('paypal.show');

    // Invoice Module
    Route::middleware(['auth', 'verified', 'company', 'tool.access:invoice'])->prefix('invoice')->name('invoice.')->group(function () {
        Route::get('/', InvoiceIndex::class)->name('index');
        Route::get('/create', InvoiceCreate::class)->name('create');
        Route::get('/{invoice}', InvoiceShow::class)->name('show');
        Route::get('/{invoice}/edit', InvoiceEdit::class)->name('edit');
        Route::get('/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('pdf');
    });
});

// ─── Admin Routes ───────────────────────────────────────────────────
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role');
    Route::get('/thresholds', [AdminController::class, 'thresholds'])->name('thresholds');
    Route::patch('/thresholds/{threshold}', [AdminController::class, 'updateThreshold'])->name('thresholds.update');
    Route::get('/invoicemaker', [AdminController::class, 'invoiceMaker'])->name('invoicemaker');
    Route::post('/invoicemaker', [AdminController::class, 'saveInvoiceMaker'])->name('invoicemaker.save');
    Route::post('/invoicemaker/test', [AdminController::class, 'testInvoiceMaker'])->name('invoicemaker.test');
});

require __DIR__.'/auth.php';
