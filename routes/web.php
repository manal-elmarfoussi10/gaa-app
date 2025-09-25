<?php

use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Http\Middleware\CompanyAccess;
use App\Http\Middleware\SuperAdminAccess;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ClientSignatureController;
use App\Http\Controllers\Webhooks\YousignWebhookController;
use App\Http\Middleware\VerifyCsrfToken;


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

    // Poseur dashboard (generic)
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

    // Dashboards
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/poseur', [DashboardPoseurController::class, 'index'])->name('dashboard.poseur');
    Route::get('/poseur/dossiers', [DashboardPoseurController::class, 'dossiers'])->name('poseur.dossiers');
    Route::post('/poseur/intervention/{id}/comment', [DashboardPoseurController::class, 'ajouterCommentaire'])->name('poseur.comment');

    // Clients
    Route::resource('clients', ClientController::class);
    Route::get('/clients/{client}/export-pdf', [ClientController::class, 'exportPdf'])->name('clients.export.pdf');
    Route::post('/clients/{client}/statut-interne', [ClientController::class, 'updateStatutInterne'])->name('clients.statut_interne');

    // Conversations (tenant)
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

    // Factures
    Route::resource('factures', FactureController::class);
    Route::get('/factures/export/excel', [FactureController::class, 'exportExcel'])->name('factures.export.excel');
    Route::get('/factures/export/pdf', [FactureController::class, 'exportFacturesPDF'])->name('factures.export.pdf');
    Route::get('/factures/{id}/pdf', [FactureController::class, 'downloadPdf'])->name('factures.download.pdf');
    Route::match(['get', 'post'], '/factures/{facture}/acquitter', [FactureController::class, 'acquitter'])->name('factures.acquitter');

    // Paiements
    Route::get('/paiements/create/{facture?}', [PaiementController::class, 'create'])->name('paiements.create');
    Route::post('/paiements', [PaiementController::class, 'store'])->name('paiements.store');
    Route::resource('paiements', PaiementController::class)->except(['create']);

    // Avoirs
    Route::get('/avoirs/export/excel', [AvoirController::class, 'exportExcel'])->name('avoirs.export.excel');
    Route::get('/avoirs/export/pdf', [AvoirController::class, 'exportPDF'])->name('avoirs.export.pdf');
    Route::get('/avoirs/{avoir}/pdf', [AvoirController::class, 'exportPDF'])->name('avoirs.pdf');
    Route::get('/avoirs/create/from-facture/{facture}', [AvoirController::class, 'createFromFacture'])->name('avoirs.create.fromFacture');
    Route::resource('avoirs', AvoirController::class);

    // Resources
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

// ---- CONTRACT ACTIONS ----
    // Contract PDF + e-sign
    Route::post('/clients/{client}/contract/generate', [\App\Http\Controllers\ContractController::class, 'generate'])
        ->name('clients.contract.generate');

    Route::get('/clients/{client}/contract/download', [\App\Http\Controllers\ContractController::class, 'download'])
        ->name('clients.contract.download');

    Route::get('/clients/{client}/contract/download-signed', [\App\Http\Controllers\ContractController::class, 'downloadSigned'])
        ->name('clients.contract.download_signed');

    Route::post('/clients/{client}/send-signature', [\App\Http\Controllers\ContractController::class, 'send'])
        ->name('clients.send_signature');

    Route::post('/clients/{client}/resend-signature', [\App\Http\Controllers\ContractController::class, 'resend'])
        ->name('clients.resend_signature');

        Route::post('/clients/{client}/send-signature', [\App\Http\Controllers\ClientSignatureController::class, 'send'])
    ->name('clients.send_signature');

    Route::post('/webhooks/yousign', YousignWebhookController::class)
    ->withoutMiddleware([VerifyCsrfToken::class])   // <-- no CSRF
    ->name('webhooks.yousign');// or add to $except

    Route::post('/clients/{client}/signature/refresh', function (App\Models\Client $client, App\Services\YousignService $ys) {
        if (!$client->yousign_request_id) return back();
    
        $sr = $ys->getSignatureRequest($client->yousign_request_id);
        $status = data_get($sr, 'status'); // adjust to the field name returned
    
        if (in_array($status, ['completed','done','signed'], true)) {
            $client->update(['statut_gsauto' => 'signed', 'signed_at' => now()]);
        }
    
        return back()->with('open_signature', true);
    })->name('clients.signature.refresh');


    // Bons de commande
    Route::resource('bons-de-commande', BonDeCommandeController::class)
        ->parameters(['bons-de-commande' => 'bon']);
    Route::get('bons-de-commande/export/excel', [BonDeCommandeController::class, 'exportExcel'])->name('bons-de-commande.export.excel');
    Route::get('bons-de-commande/export/pdf', [BonDeCommandeController::class, 'exportPDF'])->name('bons-de-commande.export.pdf');

    // Email templates
    Route::resource('email-templates', EmailTemplateController::class)->only(['index','store','show']);
    Route::get('/email-templates', [EmailTemplateController::class, 'inbox'])->name('email-templates.inbox');

    // Emails (tenant)
    Route::prefix('emails')->controller(EmailController::class)->group(function () {
        Route::get('/', 'inbox')->name('emails.inbox');
        Route::get('/sent', 'sent')->name('emails.sent');
        Route::get('/important', 'important')->name('emails.important');
        Route::get('/bin', 'bin')->name('emails.bin');
        Route::get('/create', 'create')->name('emails.create');
        Route::get('/notifications', 'notifications')->name('emails.notifications');

        Route::post('/mark-all-read', 'markAllRead')->name('emails.markAllRead');
        Route::post('/upload', 'upload')->name('emails.upload');

        Route::post('/', 'store')->name('emails.store');
        Route::post('/{id}/reply', 'reply')->name('emails.reply');

        Route::post('/{id}/delete', 'destroy')->name('emails.delete');
        Route::post('/{id}/restore', 'restore')->name('emails.restore');
        Route::post('/{id}/toggle-star', 'toggleStar')->name('emails.toggleStar');
        Route::delete('/{id}/permanent', 'permanentDelete')->name('emails.permanentDelete');
        Route::post('/{id}/toggle-important', 'toggleImportant')->name('emails.toggleImportant');
        Route::post('/{email}/mark-important', 'markImportant')->name('emails.markImportant');
        Route::post('/{email}/move-to-trash', 'moveToTrash')->name('emails.moveToTrash');

        Route::get('/{id}', 'show')->name('emails.show');
    });

    // Company profile
    Route::get('/profile', [CompanyController::class, 'show'])->name('company.profile');
    Route::get('/profile/edit', [CompanyController::class, 'edit'])->name('company.edit');
    Route::put('/profile/update', [CompanyController::class, 'update'])->name('company.update');
    Route::get('/profile/create', [CompanyController::class, 'create'])->name('company.create');
    Route::post('/profile', [CompanyController::class, 'store'])->name('company.store');

    // Sidexa
    Route::prefix('sidexa')->controller(SidexaController::class)->group(function () {
        Route::get('/', 'index')->name('sidexa.index');
        Route::get('/create', 'create')->name('sidexa.create');
        Route::post('/', 'store')->name('sidexa.store');
    });

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Units
    Route::get('/acheter-unites', [UnitController::class, 'showPurchaseForm'])->name('units.form');
    Route::post('/acheter-unites', [UnitController::class, 'purchase'])->name('units.purchase');

    // Misc
    Route::get('/ma-consommation', fn () => view('consommation.index'))->name('consommation.index');
    Route::view('/depenses', 'depenses.index')->name('depenses.index');
    Route::view('/fonctionnalites', 'fonctionnalites.fonctionnalites');
    Route::view('/commercial', 'commercial.dashboard')->name('commercial.dashboard');
    Route::view('/comptable', 'comptable.dashboard')->name('comptable.dashboard');
    Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
    Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

    // Global search
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');
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

    // Companies
    Route::resource('companies', SuperAdminCompanyController::class)
        ->only(['index','create','store','show','edit','update','destroy']);

    // Company users
    Route::get('companies/{company}/users/create', [SuperAdminUserController::class, 'create'])->name('companies.users.create');
    Route::post('companies/{company}/users', [SuperAdminUserController::class, 'store'])->name('companies.users.store');
    Route::get('companies/{company}/users/{user}/edit', [SuperAdminUserController::class, 'edit'])->name('companies.users.edit');
    Route::put('companies/{company}/users/{user}', [SuperAdminUserController::class, 'update'])->name('companies.users.update');
    Route::delete('companies/{company}/users/{user}', [SuperAdminUserController::class, 'destroy'])->name('companies.users.destroy');

    // Global users
    Route::resource('global-users', GlobalUserController::class)
        ->only(['index','create','store','edit','update','destroy']);

    // Products (superadmin catalogue)
    Route::resource('products', SAProductController::class)->except(['show'])->names('products');

    // Messages (global inbox)
    Route::resource('messages', SAMessageController::class)->only(['index','show','destroy'])->names('messages');

    // Emails (superadmin)
    Route::prefix('emails')->name('emails.')->controller(SAEmailController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/mark-all-read', 'markAllRead')->name('markAllRead');
        Route::post('/upload', 'upload')->name('upload');
        Route::post('/{email}/assign-receiver', 'assignReceiver')->name('assignReceiver');
        Route::post('/{email}/reply', 'reply')->name('reply');
        Route::post('/{email}/toggle-important', 'toggleImportant')->name('toggleImportant');
        Route::post('/{email}/move-to-trash', 'moveToTrash')->name('moveToTrash');
        Route::get('/{email}', 'show')->name('show');
    });
});

// ==========================================
// SUPPORT AREA (superadmin + client_service)
// ==========================================
Route::middleware(['auth','support'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        // Clients dossier (visible to superadmin + client_service)
        Route::get('/clients/{client}', [SAClientsController::class, 'show'])->name('clients.show');
        Route::get('/clients/{client}/export/pdf', [SAClientsController::class, 'exportPdf'])->name('clients.export.pdf');

        // Conversations (same permissions)
        Route::post('clients/{client}/conversations', [ConversationController::class, 'store'])
            ->name('clients.conversations.store');
        Route::post('conversations/reply/{email}', [ConversationController::class, 'reply'])
            ->name('conversations.reply');
        Route::get('conversations/fetch/{client}', [ConversationController::class, 'fetch'])
            ->name('conversations.fetch');
        Route::get('conversations/download/{reply}', [ConversationController::class, 'download'])
            ->name('conversations.download');
        Route::delete('conversations/{thread}', [ConversationController::class, 'destroyThread'])
            ->name('conversations.destroyThread');

        // Files / Emails for support
        Route::get('/files',  [SAFilesController::class,  'index'])->name('files.index');
        Route::get('/files/export', [SAFilesController::class, 'export'])->name('files.export');
        Route::get('/emails',       [SAEmailController::class, 'index'])->name('emails.index');
        Route::get('/emails/{email}', [SAEmailController::class, 'show'])->name('emails.show');
        Route::post('/emails/{email}/reply', [SAEmailController::class, 'reply'])->name('emails.reply');



    
    });

require __DIR__.'/auth.php';