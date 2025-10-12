<?php

use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Http\Middleware\CompanyAccess;
use App\Http\Middleware\SuperAdminAccess;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ClientSignatureController;

// ===============================
// Superadmin area controllers
// ===============================
use App\Http\Controllers\SuperAdmin\ClientsController as SAClientsController;
use App\Http\Controllers\SuperAdmin\ProductController as SAProductController;
use App\Http\Controllers\SuperAdmin\MessageController as SAMessageController;
use App\Http\Controllers\SuperAdmin\EmailController   as SAEmailController;
use App\Http\Controllers\SuperAdmin\FilesController   as SAFilesController;
use App\Http\Controllers\SuperAdmin\{
    SuperAdminDashboardController,
    CompanyController as SuperAdminCompanyController,
    GlobalUserController,
    UserController as SuperAdminUserController,
};

// ===============================
// Tenant area controllers
// ===============================
use App\Http\Controllers\{
    ProfileController,
    ClientController,
    RdvController,
    DevisController,
    SearchController,
    FactureController,
    PaiementController,
    AvoirController,
    FournisseurController,
    ProduitController,
    PoseurController,
    StockController,
    BonDeCommandeController,
    EmailTemplateController,
    EmailController,
    CompanyController,
    SidexaController,
    UserController,
    UnitController,
    ExpenseController,
    ContactController,
    DashboardController,
    DashboardPoseurController,
    AccountController,
    ConversationController
};

/*
|--------------------------------------------------------------------------
| PUBLIC / UTILITY
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));

Route::get('/attachment/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    abort_unless(file_exists($fullPath), 404);
    return response()->file($fullPath);
})->where('path', '.*')->name('attachment');

Route::get('/test-pdf', function () {
    $pdf = Pdf::loadHTML('<h1>Hello PDF</h1>');
    return $pdf->download('test.pdf');
});

/*
|--------------------------------------------------------------------------
| AUTH (not company-scoped)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Account
    Route::get('/mon-compte', [AccountController::class, 'show'])->name('mon-compte');
    Route::post('/mon-compte', [AccountController::class, 'update'])->name('mon-compte.update');
    Route::post('/mon-compte/mot-de-passe', [AccountController::class, 'updatePassword'])->name('mon-compte.password');
    Route::delete('/mon-compte/supprimer', [AccountController::class, 'destroy'])->name('mon-compte.delete');
    Route::post('/mon-compte/supprimer-photo', [AccountController::class, 'deletePhoto'])->name('mon-compte.photo.delete');

    // Poseur dashboard
    Route::get('/poseur/dashboard', [PoseurController::class, 'dashboard'])->name('poseur.dashboard');
    Route::post('/poseur/intervention/{id}/commenter', [PoseurController::class, 'commenter'])->name('poseur.commenter');
});

/*
|--------------------------------------------------------------------------
| TENANT (company-scoped)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', CompanyAccess::class])
    ->scopeBindings()
    ->group(function () {

    // (removed duplicate: clients.contract.download_signed on ClientSignatureController)

    // Dashboards
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/poseur', [DashboardPoseurController::class, 'index'])->name('dashboard.poseur');
    Route::get('/poseur/dossiers', [DashboardPoseurController::class, 'dossiers'])->name('poseur.dossiers');
    Route::post('/poseur/intervention/{id}/comment', [DashboardPoseurController::class, 'ajouterCommentaire'])->name('poseur.comment');

    // Clients
    Route::resource('clients', ClientController::class);
    Route::get('/clients/{client}/export-pdf', [ClientController::class, 'exportPdf'])->name('clients.export.pdf');
    Route::post('/clients/{client}/statut-interne', [ClientController::class, 'updateStatutInterne'])->name('clients.statut_interne');

    // Conversations
    Route::post('clients/{client}/conversations', [ConversationController::class, 'store'])->name('clients.conversations.store');
    Route::get('clients/{client}/conversation', [ConversationController::class, 'show'])->name('clients.conversation');
    Route::post('/conversations/reply/{email}', [ConversationController::class, 'reply'])->name('conversations.reply');
    Route::delete('conversations/{thread}', [ConversationController::class, 'destroyThread'])->name('conversations.destroyThread');
    Route::get('conversations/download/{reply}', [ConversationController::class, 'download'])->name('conversations.download');
    Route::get('conversations/fetch/{client}', [ConversationController::class, 'fetch'])->name('conversations.fetch');

    // Calendar
    Route::get('/calendar', [RdvController::class, 'calendar'])->name('rdv.calendar');
    Route::get('/calendar/events', [RdvController::class, 'events'])->name('rdv.events');
    Route::resource('rdv', RdvController::class)->except(['create', 'edit', 'show']);

    // Devis
    Route::resource('devis', DevisController::class);
    Route::get('/devis/export/excel', [DevisController::class, 'exportExcel'])->name('devis.export.excel');
    Route::get('/devis/export/pdf', [DevisController::class, 'exportPDF'])->name('devis.export.pdf');
    Route::post('/devis/{devis}/generate-facture', [DevisController::class, 'generateFacture'])->name('devis.generate.facture');
    Route::get('/devis/{id}/pdf', [DevisController::class, 'downloadSinglePdf'])->name('devis.download.pdf');
    Route::get('/devis/{id}/preview', [DevisController::class, 'previewPdf'])->name('devis.preview');

    // Factures
    Route::resource('factures', FactureController::class);
    Route::get('/factures/export/excel', [FactureController::class, 'exportExcel'])->name('factures.export.excel');
    Route::get('/factures/export/pdf', [FactureController::class, 'exportFacturesPDF'])->name('factures.export.pdf');
    Route::get('/factures/{id}/pdf', [FactureController::class, 'downloadPdf'])->name('factures.download.pdf');
    Route::match(['get', 'post'], '/factures/{facture}/acquitter', [FactureController::class, 'acquitter'])->name('factures.acquitter');
    Route::get('/factures/{id}/preview', [FactureController::class, 'previewPdf'])->name('factures.preview');

    // Avoirs
    Route::get('/avoirs/export/excel', [AvoirController::class, 'exportExcel'])->name('avoirs.export.excel');
    Route::get('/avoirs/export/pdf', [AvoirController::class, 'exportPDF'])->name('avoirs.export.pdf');
    Route::get('/avoirs/{avoir}/pdf', [AvoirController::class, 'exportPDF'])->name('avoirs.pdf');
    Route::get('/avoirs/create/from-facture/{facture}', [AvoirController::class, 'createFromFacture'])->name('avoirs.create.fromFacture'];
    Route::resource('avoirs', AvoirController::class);
    Route::get('/avoirs/{id}/preview', [AvoirController::class,'previewPdf'])->name('avoirs.preview');

    // Paiements
    Route::get('/paiements/create/{facture?}', [PaiementController::class, 'create'])->name('paiements.create');
    Route::post('/paiements', [PaiementController::class, 'store'])->name('paiements.store');
    Route::resource('paiements', PaiementController::class)->except(['create']);

    // Fournisseurs, Produits, etc.
    Route::resources([
        'fournisseurs' => FournisseurController::class,
        'produits'     => ProduitController::class,
        'poseurs'      => PoseurController::class,
        'stocks'       => StockController::class,
        'expenses'     => ExpenseController::class,
    ]);

    // Exports
    Route::get('/stocks/export/excel', [StockController::class, 'exportExcel'])->name('stocks.export.excel');
    Route::get('/stocks/export/pdf', [StockController::class, 'exportPDF'])->name('stocks.export.pdf');
    Route::get('/expenses/export/excel', [ExpenseController::class, 'exportExcel'])->name('expenses.export.excel');
    Route::get('/expenses/export/pdf', [ExpenseController::class, 'exportPDF'])->name('expenses.export.pdf');

    // Contracts
    Route::post('/clients/{client}/contract/generate', [ContractController::class, 'generate'])->name('clients.contract.generate');
    Route::get('/clients/{client}/contract/download', [ContractController::class, 'download'])->name('clients.contract.download');
    Route::get('/clients/{client}/contract/download-signed', [ContractController::class, 'downloadSigned'])->name('clients.contract.download_signed');
    Route::post('/clients/{client}/send-signature', [ContractController::class, 'send'])->name('clients.send_signature');
    Route::post('/clients/{client}/resend-signature', [ContractController::class, 'resend'])->name('clients.resend_signature');

    // Company profile
    Route::get('/profile', [CompanyController::class, 'show'])->name('company.profile');
    Route::get('/profile/edit', [CompanyController::class, 'edit'])->name('company.edit');
    Route::put('/profile/update', [CompanyController::class, 'update'])->name('company.update');
    Route::get('/profile/create', [CompanyController::class, 'create'])->name('company.create');
    Route::post('/profile', [CompanyController::class, 'store'])->name('company.store');
});

/*
|--------------------------------------------------------------------------
| SUPERADMIN (superadmin only)
|--------------------------------------------------------------------------
*/
Route::prefix('superadmin')
    ->middleware(['auth', SuperAdminAccess::class])
    ->name('superadmin.')
    ->group(function () {

    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

    // Files & Previews
    Route::get('/files',  [SAFilesController::class, 'index'])->name('files.index');
    Route::get('/files/export', [SAFilesController::class, 'export'])->name('files.export');
    Route::get('/files/preview/{type}/{id}', [SAFilesController::class, 'preview'])
        ->where(['type' => 'devis|factures|avoirs', 'id' => '\d+'])
        ->name('files.preview');

    // PDF Previews (superadmin)
    Route::get('/devis/{devis}/preview',    [SAClientsController::class, 'previewDevis'])->name('devis.preview');
    Route::get('/factures/{facture}/preview',[SAClientsController::class, 'previewFacture'])->name('factures.preview');
    Route::get('/avoirs/{avoir}/preview',   [SAClientsController::class, 'previewAvoir'])->name('avoirs.preview');

    // Companies
    Route::resource('companies', SuperAdminCompanyController::class);

    // Products
    Route::resource('products', SAProductController::class)->except(['show'])->names('products');

    // Messages
    Route::resource('messages', SAMessageController::class)->only(['index','show','destroy'])->names('messages');

    // Emails
    Route::prefix('emails')->name('emails.')->controller(SAEmailController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/mark-all-read', 'markAllRead')->name('markAllRead');
        Route::post('/upload', 'upload')->name('upload');
        Route::get('/{email}', 'show')->name('show');
    });
});

/*
|--------------------------------------------------------------------------
| SUPPORT (superadmin + client_service)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','support'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/clients/{client}', [SAClientsController::class, 'show'])->name('clients.show');
        Route::get('/clients/{client}/export/pdf', [SAClientsController::class, 'exportPdf'])->name('clients.export.pdf');
    });

require __DIR__.'/auth.php';