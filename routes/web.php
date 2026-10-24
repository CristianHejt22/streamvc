<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StreamingAccountController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\ResellerController;
use App\Http\Controllers\TreasuryController;
use App\Http\Controllers\ShortcutController;
use App\Http\Controllers\WhiteLabelController;
use App\Http\Controllers\InstallationController;

/*
|--------------------------------------------------------------------------
| Public Routes (Catálogo Digital & Checkout Frontend)
|--------------------------------------------------------------------------
*/
Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/ref/{code}', function ($code) {
    return redirect()->route('catalog.index', ['ref' => $code]);
})->name('catalog.referral');
Route::get('/p/{code}', function ($code) {
    return redirect()->route('catalog.index', ['ref' => $code]);
})->name('catalog.panel');
Route::get('/panel/{code}', function ($code) {
    return redirect()->route('catalog.index', ['ref' => $code]);
});

// Diagnóstico de Salud y Base de Datos (Especial para Vercel)
Route::get('/health', function () {
    $dbConfig = config('database.default');
    $dbHost = config("database.connections.{$dbConfig}.host");
    $dbPort = config("database.connections.{$dbConfig}.port");
    $dbName = config("database.connections.{$dbConfig}.database");
    $dbUser = config("database.connections.{$dbConfig}.username");

    $connected = false;
    $error = null;
    $tables = [];

    try {
        \DB::connection()->getPdo();
        $connected = true;
        $tables = \DB::select('SHOW TABLES');
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }

    return response()->json([
        'status' => $connected ? 'ok' : 'database_error',
        'server' => 'Vercel Serverless PHP ' . PHP_VERSION,
        'app_env' => config('app.env'),
        'app_debug' => config('app.debug'),
        'database' => [
            'driver' => $dbConfig,
            'host' => $dbHost,
            'port' => $dbPort,
            'database' => $dbName,
            'user' => $dbUser,
            'connected' => $connected,
            'tables_count' => count($tables),
            'error' => $error,
        ],
    ]);
});

// Asistente de Migraciones en la Nube (Seguro mediante token)
Route::get('/migrate-setup', function (\Illuminate\Http\Request $request) {
    if ($request->query('key') !== 'cstreame2026') {
        abort(403, 'Acceso no autorizado. Se requiere clave de seguridad ?key=cstreame2026');
    }

    try {
        \DB::connection()->getPdo();
    } catch (\Throwable $e) {
        return response("<h3>Error de Conexión a la Base de Datos</h3><p>{$e->getMessage()}</p><p>Verifica que DB_HOST, DB_DATABASE, DB_USERNAME y DB_PASSWORD estén configurados en Vercel.</p>", 500);
    }

    $output = '';
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output .= "<h4>1. Migraciones:</h4><pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";

        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        $output .= "<h4>2. Datos Iniciales (Seeders):</h4><pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";

        return response("<h2>¡Base de Datos Inicializada con Éxito en Vercel!</h2>" . $output . "<p><a href='/'>Ir al Catálogo Principal</a> | <a href='/login'>Iniciar Sesión</a></p>");
    } catch (\Throwable $e) {
        return response("<h3>Error ejecutando migraciones:</h3><pre>{$e->getMessage()}</pre>", 500);
    }
});

// PWA Manifest & App Installation Routes (URL corta para compartir por WhatsApp)
Route::get('/sw.js', function () {
    return response()->file(public_path('sw.js'), [
        'Content-Type' => 'application/javascript',
        'Service-Worker-Allowed' => '/',
    ]);
});
Route::get('/manifest.json', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/app/{code?}', [\App\Http\Controllers\PwaController::class, 'installPage'])->name('pwa.install');
Route::get('/instalar/{code?}', [\App\Http\Controllers\PwaController::class, 'installPage'])->name('pwa.instalar');

Route::get('/checkout/{plan}', [CatalogController::class, 'checkout'])->name('catalog.checkout');
Route::post('/checkout/{plan}', [CatalogController::class, 'processCheckout'])->name('catalog.checkout.process');
Route::get('/checkout/mercadopago/{order}', [CatalogController::class, 'mpRedirect'])->name('catalog.mp_redirect');
Route::post('/checkout/mercadopago/{order}/simulate', [CatalogController::class, 'mpSimulatePayment'])->name('catalog.mp_simulate');
Route::get('/orden/{order}/exito', [CatalogController::class, 'success'])->name('catalog.success');

/*
|--------------------------------------------------------------------------
| Portal del Cliente & Bóveda de Streaming (Registro & Login por Correo y Contraseña)
|--------------------------------------------------------------------------
*/
Route::get('/registro', [\App\Http\Controllers\CustomerPortalController::class, 'showRegister'])->name('portal.register');
Route::post('/registro', [\App\Http\Controllers\CustomerPortalController::class, 'processRegister'])->name('portal.register.process');
Route::get('/ref/{code}/registro', [\App\Http\Controllers\CustomerPortalController::class, 'showRegister'])->name('portal.register.ref');

// Formulario de Afiliación de Revendedor enviado por el Proveedor
Route::get('/afiliar/{code}', [\App\Http\Controllers\ResellerController::class, 'showAffiliateRegister'])->name('affiliate.register');
Route::post('/afiliar/{code}', [\App\Http\Controllers\ResellerController::class, 'processAffiliateRegister'])->name('affiliate.register.process');
Route::get('/registro-revendedor/{code}', [\App\Http\Controllers\ResellerController::class, 'showAffiliateRegister'])->name('resellers.affiliate_register');
Route::post('/registro-revendedor/{code}', [\App\Http\Controllers\ResellerController::class, 'processAffiliateRegister'])->name('resellers.affiliate_register.process');

Route::get('/boveda/login', [\App\Http\Controllers\CustomerPortalController::class, 'showLogin'])->name('boveda.login');
Route::post('/boveda/login', [\App\Http\Controllers\CustomerPortalController::class, 'processLogin'])->name('boveda.login.process');
Route::post('/boveda/logout', [\App\Http\Controllers\CustomerPortalController::class, 'logout'])->name('boveda.logout');

Route::middleware(['auth:customer'])->group(function () {
    Route::get('/boveda', [\App\Http\Controllers\CustomerPortalController::class, 'index'])->name('boveda.index');
    Route::post('/boveda/report-issue/{sale}', [\App\Http\Controllers\CustomerPortalController::class, 'reportIssue'])->name('boveda.report');
    Route::post('/boveda/renovar/{sale}', [\App\Http\Controllers\CustomerPortalController::class, 'submitRenewalRequest'])->name('boveda.renovar');
    Route::post('/boveda/verify-unlock', [\App\Http\Controllers\CustomerPortalController::class, 'verifyUnlock'])->name('boveda.verify_unlock');
    Route::post('/boveda/update-pin', [\App\Http\Controllers\CustomerPortalController::class, 'updatePin'])->name('boveda.update_pin');
});

/*
|--------------------------------------------------------------------------
| Installation & Wizard Routes
|--------------------------------------------------------------------------
*/
Route::get('/install', [InstallationController::class, 'index'])->name('install.index');
Route::post('/install/test-db', [InstallationController::class, 'testDatabase'])->name('install.test_db');
Route::post('/install/verify-license', [InstallationController::class, 'verifyLicense'])->name('install.verify_license');
Route::post('/install/process', [InstallationController::class, 'process'])->name('install.process');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Panel Routes (Admin & Revendedores)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Ventas & CRM de Cuentas Streaming (Usuario, Contraseña, Perfiles, Vencimiento, WhatsApp)
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/export', [SaleController::class, 'exportCsv'])->name('sales.export');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::post('/sales/{sale}/renew', [SaleController::class, 'renew'])->name('sales.renew');
    Route::post('/sales/renewal-requests/{renewalRequest}/approve', [SaleController::class, 'approveRenewalRequest'])->name('sales.renewals.approve');
    Route::post('/sales/renewal-requests/{renewalRequest}/reject', [SaleController::class, 'rejectRenewalRequest'])->name('sales.renewals.reject');
    Route::post('/sales/renewal-requests/{renewalRequest}/request-provider-review', [SaleController::class, 'requestProviderReview'])->name('sales.renewals.request_provider_review');
    Route::post('/sales/{sale}/accept-transfer', [SaleController::class, 'acceptTransferPayment'])->name('sales.accept_transfer');
    Route::post('/sales/{sale}/pay-consignment', [SaleController::class, 'payConsignment'])->name('sales.pay_consignment');
    Route::post('/sales/{sale}/warranty', [SaleController::class, 'replaceWarranty'])->name('sales.warranty');
    Route::post('/sales/{sale}/reminder-sent', [SaleController::class, 'markReminderSent'])->name('sales.reminder_sent');
    Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');

    // Inventario de Cuentas Maestras y Perfiles
    Route::get('/accounts', [StreamingAccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [StreamingAccountController::class, 'store'])->name('accounts.store');
    Route::post('/accounts/quick-replenish', [StreamingAccountController::class, 'quickReplenish'])->name('accounts.quick_replenish');
    Route::post('/accounts/{account}/private-password', [StreamingAccountController::class, 'updatePrivatePassword'])->name('accounts.private_password');
    Route::put('/accounts/{account}', [StreamingAccountController::class, 'update'])->name('accounts.update');
    Route::post('/accounts/{account}/toggle', [StreamingAccountController::class, 'toggleStatus'])->name('accounts.toggle');
    Route::delete('/accounts/{account}', [StreamingAccountController::class, 'destroy'])->name('accounts.destroy');

    // Plataformas y Servicios de Streaming Disponibles
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::post('/services/clone-catalog', [ServiceController::class, 'cloneCatalog'])->name('services.clone_catalog');
    Route::post('/services/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('services.toggle_status');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
    Route::post('/services/{service}/plans', [ServiceController::class, 'storePlan'])->name('services.plans.store');
    Route::post('/services/plans/{plan}/toggle', [ServiceController::class, 'togglePlan'])->name('services.plans.toggle');

    // Agenda de Clientes CRM
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::post('/customers/{customer}/activate', [CustomerController::class, 'activateAccount'])->name('customers.activate');
    Route::post('/customers/{customer}/reset-password', [CustomerController::class, 'resetPassword'])->name('customers.reset_password');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Proveedores de Cuentas
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::post('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle_status');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    // Garantías & Caídas
    Route::get('/warranties', [WarrantyController::class, 'index'])->name('warranties.index');
    Route::post('/warranties', [WarrantyController::class, 'store'])->name('warranties.store');
    Route::post('/warranties/{warranty}/resolve', [WarrantyController::class, 'resolve'])->name('warranties.resolve');

    // Generador de Revendedores & Billetera de Saldo (Solo Admin)
    Route::get('/resellers', [ResellerController::class, 'index'])->name('resellers.index');
    Route::post('/resellers', [ResellerController::class, 'store'])->name('resellers.store');
    Route::post('/resellers/{reseller}/recharge', [ResellerController::class, 'rechargeBalance'])->name('resellers.recharge');
    Route::post('/resellers/{reseller}/toggle', [ResellerController::class, 'toggleStatus'])->name('resellers.toggle');
    Route::get('/resellers/{reseller}/reveal-password', [ResellerController::class, 'revealPassword'])->name('resellers.reveal_password');
    Route::post('/resellers/{reseller}/update-password', [ResellerController::class, 'updatePassword'])->name('resellers.update_password');
    Route::post('/resellers/{reseller}/clone-panel', [ResellerController::class, 'clonePanel'])->name('resellers.clone_panel');
    Route::post('/resellers/{reseller}/toggle-store-paid', [ResellerController::class, 'toggleStorePaidByReseller'])->name('resellers.toggle_store_paid');
    Route::post('/resellers/{reseller}/toggle-panel-offer', [ResellerController::class, 'togglePanelOffer'])->name('resellers.toggle_panel_offer');
    Route::post('/resellers/{reseller}/update-panel-pricing', [ResellerController::class, 'updatePanelPricing'])->name('resellers.update_panel_pricing');
    Route::post('/resellers/{reseller}/update-service-commissions', [ResellerController::class, 'updateServiceCommissions'])->name('resellers.update_service_commissions');
    Route::post('/resellers/{reseller}/update-commissions', [ResellerController::class, 'updateCommissions'])->name('resellers.update_commissions');
    Route::post('/resellers/{reseller}/reset-password', [ResellerController::class, 'resetPassword'])->name('resellers.reset_password');
    Route::post('/resellers/{reseller}/sell-credits', [ResellerController::class, 'sellCredits'])->name('resellers.sell_credits');
    Route::post('/resellers/{reseller}/activate-master', [ResellerController::class, 'activatePanelMaster'])->name('resellers.activate_master');
    Route::post('/resellers/{reseller}/activate-credit', [ResellerController::class, 'activateWithCredit'])->name('resellers.activate_credit');
    Route::post('/resellers/{reseller}/reject-request', [ResellerController::class, 'rejectPendingRequest'])->name('resellers.reject_request');
    Route::post('/resellers/{reseller}/toggle-provider-override', [ResellerController::class, 'toggleProviderWhatsAppOverride'])->name('resellers.toggle_provider_override');
    Route::post('/resellers/{reseller}/update-whatsapp-preference', [ResellerController::class, 'updatePanelWhatsAppPreference'])->name('resellers.update_whatsapp_preference');
    Route::post('/resellers/{reseller}/update-plan-prices', [ResellerController::class, 'updatePlanPrices'])->name('resellers.update_plan_prices');
    Route::post('/services/plans/update-my-pvp', [ResellerController::class, 'updateMyPvp'])->name('services.plans.update_my_pvp');
    Route::post('/resellers/balance-transfers', [ResellerController::class, 'storeBalanceTransfer'])->name('resellers.balance_transfers.store');
    Route::get('/resellers/balance-transfers/{transfer}/receipt', [ResellerController::class, 'showBalanceTransferReceipt'])->name('resellers.balance_transfers.receipt');
    Route::post('/resellers/balance-transfers/{transfer}/approve', [ResellerController::class, 'approveBalanceTransfer'])->name('resellers.balance_transfers.approve');
    Route::post('/resellers/balance-transfers/{transfer}/reject', [ResellerController::class, 'rejectBalanceTransfer'])->name('resellers.balance_transfers.reject');
    Route::post('/resellers/upgrade-admin-panel', [ResellerController::class, 'upgradeToSuperReseller'])->name('resellers.upgrade_admin_panel');
    Route::post('/resellers/{reseller}/toggle-role', [ResellerController::class, 'toggleRole'])->name('resellers.toggle_role');
    Route::post('/admin/settings/whatsapp-support', [ResellerController::class, 'updateWhatsAppSupportSettings'])->name('admin.settings.whatsapp_support');

    // Módulo de Tesorería Multi-Usuario & Mini Nube de Comprobantes
    Route::get('/treasury', [TreasuryController::class, 'index'])->name('treasury.index');
    Route::get('/treasury/export', [TreasuryController::class, 'exportCsv'])->name('treasury.export');
    Route::get('/treasury/settings', [TreasuryController::class, 'settings'])->name('treasury.settings');
    Route::post('/treasury/settings', [TreasuryController::class, 'updateSettings'])->name('treasury.settings.update');
    Route::get('/treasury/receipts/{order}', [TreasuryController::class, 'viewReceipt'])->name('treasury.receipt');
    Route::post('/treasury/orders/{order}/approve', [TreasuryController::class, 'approveAndClose'])->name('treasury.approve');
    Route::post('/treasury/orders/{order}/reject', [TreasuryController::class, 'rejectAndClose'])->name('treasury.reject');

    // Gestor de Atajos (Dock rápido)
    Route::get('/shortcuts', [ShortcutController::class, 'index'])->name('shortcuts.index');
    Route::post('/shortcuts', [ShortcutController::class, 'store'])->name('shortcuts.store');
    Route::delete('/shortcuts/{shortcut}', [ShortcutController::class, 'destroy'])->name('shortcuts.destroy');

    // Marca Blanca & Customización
    Route::get('/whitelabel', [WhiteLabelController::class, 'index'])->name('whitelabel.index');
    Route::post('/whitelabel', [WhiteLabelController::class, 'update'])->name('whitelabel.update');
    Route::get('/marca-blanca', function () {
        return redirect()->route('whitelabel.index');
    });
});
