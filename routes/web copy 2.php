<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreInventoryController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
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
    Route::get('/checkout', [CheckoutController::class, 'showCheckoutPage'])->name('checkout.page');
    Route::post('/cart/clear', [CheckoutController::class, 'clearCart'])->name('cart.clear');
    Route::post('/checkout', [CheckoutController::class, 'processCheckout'])->name('checkout');
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
