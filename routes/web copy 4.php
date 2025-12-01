<?php
use Inertia\Inertia;
use App\Http\Livewire\ProductList;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProductController,
    StoreInventoryController,
    StoreController,
    CartController,
    TransactionController,
    POSController,
    CheckoutController,
    ReceiptController,
    DashboardController,
    Auth\RegisterController,
    OrderItemController,
    InventoryController,
    Admin\SettingsController,
    UserController,
    OrderController,
    PurchaseController,
    CashOutController,
    UserTotalsController,
    SubscriptionController,
    SubscriptionStatus,
    ProductCartController,
    DailyRecordController,
    ExpenseController,
    DailyExpenseController,
};
Route::get('/products/livewire', ProductList::class)->name('products.livewire');
Route::get('/api/products', [App\Http\Controllers\ProductController::class, 'index'])
    ->name('products.fetch');
Route::get('/pos-dashboard', function () {
    return Inertia::render('Dashboard'); // React component in resources/js/Pages/Dashboard.jsx
})->name('pos.dashboard');
Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show'])->name('subscriptions.show');


Route::middleware(['auth'])->group(function () {
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');

    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
Route::get('/daily/export-excel', [DailyRecordController::class, 'exportExcel'])->name('daily.export.excel');
Route::get('/daily/export-pdf', [DailyRecordController::class, 'exportPdf'])->name('daily.export.pdf');
    // Report route(s)
    Route::get('/expenses/report', [ExpenseController::class, 'report'])->name('expenses.report');
});

Route::get('/products/sync', function () {
    return Product::with('store_inventories')->get();
})->name('products.sync');
Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show'])->name('subscriptions.show');

Route::get('/daily', [DailyRecordController::class, 'index'])->name('daily.index');

Route::get('/daily/export', [DailyRecordController::class, 'export'])->name('daily.export');
/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Subscriptions
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('/subscription/create', [SubscriptionController::class, 'create'])->name('subscription.create');
    Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store');

    // Purchases / Invoices
    Route::get('invoice/{purchase_id}', [PurchaseController::class, 'generateInvoice'])->name('invoice.generate');

    // Daily Records
    Route::resource('daily', DailyRecordController::class)->except(['show']);
    Route::get('/daily/report', [DailyRecordController::class, 'report'])->name('daily.report');
    Route::get('/daily/report/excel', [DailyRecordController::class, 'exportExcel'])->name('daily.report.excel');
    Route::get('/daily/report/pdf', [DailyRecordController::class, 'exportPdf'])->name('daily.report.pdf');
Route::delete('/daily/{id}', [DailyRecordController::class, 'destroy'])->name('daily.delete');

// In routes/web.php
Route::put('/daily/{dailyRecord}', [DailyRecordController::class, 'update'])->name('daily.update');
    // Expenses
    Route::delete('/expenses/{id}', [DailyRecordController::class, 'deleteExpense'])->name('expenses.delete');
    Route::delete('/expenses/{id}', [DailyExpenseController::class, 'destroy']); // if both needed, consider merging
});


// Route for searching products by name or barcode
Route::get('/search', [ProductCartController::class, 'search'])->name('search.products');

// Route for adding product to cart
Route::post('/cart/add', [ProductCartController::class, 'addToCart'])->name('cart.add');

// Optional route for viewing the cart (if needed)
Route::get('/cart', [ProductCartController::class, 'viewCart'])->name('cart.view');

Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
Route::middleware('auth')->group(function () {
    Route::get('/cashout', [CashOutController::class, 'index'])->name('cashout.index');
    Route::post('/cashout', [CashOutController::class, 'store'])->name('cashout.store');
    Route::get('/cashout/export', [CashOutController::class, 'export'])->name('cashout.export');
    Route::get('/user/totals/export', [UserTotalsController::class, 'export'])->name('user.totals.export');
    Route::get('/receipt-form', [ReceiptController::class, 'showForm'])->name('receipt.form');
});
Route::post('/store-inventory/add', [StoreInventoryController::class, 'store'])->name('store-inventory.add');
Route::post('/store-inventory/store', [StoreInventoryController::class, 'store'])->name('store-inventory.store');


Route::get('/search/products', [StoreInventoryController::class, 'searchProducts'])->name('search.products');
Route::get('/store-inventories/{inventoryId}/purchase', [StoreInventoryController::class, 'showPurchaseForm'])->name('store-inventories.show-purchase-form');
Route::get('/search-products', [StoreInventoryController::class, 'searchProducts'])->name('search.products');

Route::post('/store-inventories/{inventoryId}/purchase', [StoreInventoryController::class, 'addPurchase'])->name('store-inventories.add-purchase');
Route::get('/inventory/search-products', [InventoryController::class, 'searchProducts'])->name('inventory.search-products');

Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');


Route::post('/store-inventories/{inventory}/add-purchase', [StoreInventoryController::class, 'addPurchase'])->name('store-inventories.addPurchase');
Route::resource('store-inventories', StoreInventoryController::class);
Route::prefix('store-inventories')->group(function () {
    Route::get('/', [StoreInventoryController::class, 'index'])->name('store-inventories.index');
    Route::get('/create', [StoreInventoryController::class, 'create'])->name('store-inventories.create');
    Route::post('/', [StoreInventoryController::class, 'store'])->name('store-inventories.store');
    Route::get('/{storeId}', [StoreInventoryController::class, 'show'])->name('store-inventories.show');
});
Route::post('/users/{user}/assign-store', [UserController::class, 'assignUserToStore']);
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
});

// Public Routes
Route::get('/', function () {
    return view('auth/login');
});
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::get('/offline_receipt', [ReceiptController::class, 'showOfflineReceipt'])->name('offline.receipt');

// Routes accessible only to authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/cashout/create', [CashOutController::class, 'create'])->name('cashout.create');
    Route::post('/cashout', [CashOutController::class, 'store'])->name('cashout.store');
    
    // If you want a route to view the cashout index, you should define it as a GET request.
    Route::get('/cashout', [CashOutController::class, 'index'])->name('cashout.index');
    
    // web.php
Route::get('/cashout/maxBalance', function () {
    $maxBalance = auth()->user()->getCashBalanceForToday();
    return response()->json([
        'max_balance' => $maxBalance,
        'max_balance_formatted' => number_format($maxBalance, 2),
    ]);
})->name('cashout.maxBalance');
Route::get('/totals-per-user-and-store', [DashboardController::class, 'totalsPerUserAndStore'])->name('totals.user.store');
Route::get('/cashout/export', [CashOutController::class, 'export'])->name('cashout.export');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/user-totals', [DashboardController::class, 'userTotals'])->name('user.totals');
    Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
    Route::get('/store/{store}', [StoreController::class, 'show'])->name('store.show');
    Route::get('/stores/{storeId}', [StoreController::class, 'show'])->name('stores.show');
    
    Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');
    Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
    Route::get('/order-items', [OrderItemController::class, 'index'])->name('order.items');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderId}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/orders/{id}/items', [OrderController::class, 'show']);
    Route::get('/inventory/stock', [StoreInventoryController::class, 'getStock'])->name('inventory.get-stock');
    Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
    Route::get('/receipt', [CheckoutController::class, 'showReceipt'])->name('receipt');
    Route::get('/receipt/{orderId}', [ReceiptController::class, 'showReceipt'])->name('receipt.show');
    Route::get('/pos/checkout', [OrderController::class, 'checkout'])->name('pos.checkout');
    Route::post('/inventory/update/{id}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::get('/inventory/top-up', [InventoryController::class, 'showTopUpForm'])->name('inventory.top-up');
    Route::post('/inventory/top-up', [InventoryController::class, 'processTopUp'])->name('inventory.top-up.process');
    Route::post('/checkout/process', [CheckoutController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/checkout/success', [CheckoutController::class, 'checkoutSuccess'])->name('checkout.success');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

    Route::get('/checkout', [CheckoutController::class, 'showCheckoutPage'])->name('checkout.page');
    Route::post('/cart/clear', [CheckoutController::class, 'clearCart'])->name('cart.clear');
    Route::post('/checkout', [CheckoutController::class, 'processCheckout'])->name('checkout');
    Route::get('/orders/{id}/receipt', [CheckoutController::class, 'showReceipt'])->name('orders.receipt');
    Route::post('/clear-cart', [CheckoutController::class, 'clearCart'])->name('cart.clear');
    Route::get('/store_inventories/create', [StoreInventoryController::class, 'create'])->name('store_inventories.create');
    Route::get('/pos', [CartController::class, 'index'])->name('pos.index');
    Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');
    Route::get('store_inventories/{store_id}', [StoreInventoryController::class, 'show'])->name('store_inventories.show');
    Route::resource('stores', StoreController::class);
    Route::resource('store_inventories', StoreInventoryController::class);
    Route::get('stores/{store}/products', [StoreController::class, 'products'])->name('stores.products');
    Route::resource('transactions', TransactionController::class);
    Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('/checkout/cash', [CheckoutController::class, 'cashCheckout'])->name('cash.checkout');
    Route::post('/checkout/pos', [CheckoutController::class, 'posCheckout'])->name('pos.checkout');
    Route::get('/transactions/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/checkout/cash', [CheckoutController::class, 'cashCheckout'])->name('cart.checkout.cash');
    Route::post('/checkout/pos', [CheckoutController::class, 'posCheckout'])->name('cart.checkout.pos');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/products/cards', [ProductController::class, 'cards'])->name('products.cards');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::get('/product/{product}', [ProductController::class, 'showProductWithCart'])->name('product.show');

    Route::get('/products/download/csv', [ProductController::class, 'downloadCsv'])->name('products.download.csv');
Route::get('/products/download/pdf', [ProductController::class, 'downloadPdf'])->name('products.download.pdf');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/select-store', [CartController::class, 'selectStorePage'])->name('cart.selectStore');
    Route::resource('products', ProductController::class);
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings/update', [SettingsController::class, 'update'])->name('admin.settings.update');
});
