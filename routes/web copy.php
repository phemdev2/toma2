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
// routes/web.php
use App\Http\Controllers\DashboardController;
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
// routes/web.php
use App\Http\Controllers\Auth\RegisterController;
Route::get('/offline_receipt', [YourController::class, 'showOfflineReceipt'])->name('offline.receipt');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');

use App\Http\Controllers\OrderItemController;
Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');

// Route to show a specific store's details
Route::get('/stores/{storeId}', [StoreController::class, 'show'])->name('stores.show');

// Route to show the form for creating a new store
Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');

// Route to store a new store
Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
Route::get('/order-items', [OrderItemController::class, 'index'])->name('order.items');

Route::get('/inventory/stock', [StoreInventoryController::class, 'getStock'])->name('inventory.get-stock');



Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
Route::get('/receipt', [CheckoutController::class, 'showReceipt'])->name('receipt');

Route::get('/receipt/{orderId}', [ReceiptController::class, 'showReceipt'])->name('receipt.show');
Route::get('/offline_receipt', [ReceiptController::class, 'showOfflineReceipt'])->name('offline.receipt');
Route::get('/pos/checkout', [OrderController::class, 'checkout'])->name('pos.checkout');


// Define the route for the receipt page
Route::get('/receipt', [ReceiptController::class, 'show'])->name('receipt');

// Other routes for your application
Route::get('/', function () {
    return view('welcome');
});



use App\Http\Controllers\InventoryController;
Route::get('/receipt/offline', function () {
    return view('offline_receipt');
});
Route::get('/inventory/top-up', [InventoryController::class, 'showTopUpForm'])->name('inventory.top-up');
Route::post('/inventory/top-up', [InventoryController::class, 'processTopUp'])->name('inventory.top-up.process');


Route::post('/inventory/update/{id}', [InventoryController::class, 'update'])->name('inventory.update');
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/inventory/top-up', [InventoryController::class, 'showTopUpForm'])->name('inventory.top-up.form');
Route::post('/inventory/top-up', [InventoryController::class, 'topUp'])->name('inventory.top-up');

Route::post('/checkout/process', [CheckoutController::class, 'processCheckout'])->name('checkout.process');
Route::post('/checkout', [CheckoutController::class, 'processCheckout'])->name('checkout.process');
Route::get('/checkout/success', [CheckoutController::class, 'checkoutSuccess'])->name('checkout.success');
Route::get('/checkout', [CheckoutController::class, 'showCheckoutPage'])->name('checkout.page');
Route::post('/cart/clear', [CheckoutController::class, 'clearCart'])->name('cart.clear');
Route::post('/checkout', 'CheckoutController@processCheckout')->name('checkout.process');

Route::post('/checkout', [CheckoutController::class, 'processCheckout'])->name('checkout');
Route::post('/clear-cart', [CheckoutController::class, 'clearCart'])->name('cart.clear');
Route::get('/store_inventories/create', [StoreInventoryController::class, 'create'])->name('store_inventories.create');
Route::get('/pos', [CartController::class, 'index'])->name('pos.index');
Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');
Route::get('store_inventories/{store_id}', [StoreInventoryController::class, 'show'])->name('store_inventories.show');
Route::resource('stores', StoreController::class);
Route::resource('store_inventories', StoreInventoryController::class);
// Add a custom route for fetching products by store
Route::get('stores/{store}/products', [StoreController::class, 'products'])->name('stores.products');
Route::get('/store/{store}', [StoreController::class, 'show'])->name('store.show');
Route::resource('transactions', TransactionController::class);
Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');
Route::post('/checkout/cash', [CheckoutController::class, 'cashCheckout'])->name('cash.checkout');
Route::post('/checkout/pos', [CheckoutController::class, 'posCheckout'])->name('pos.checkout');
Route::get('/receipt/{id}', [CheckoutController::class, 'showReceipt'])->name('receipt');

Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
Route::get('transactions/{id}', [TransactionController::class, 'show']);
Route::get('/transactions/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

Route::post('/checkout/cash', [CheckoutController::class, 'cashCheckout'])->name('cart.checkout.cash');
Route::post('/checkout/pos', [CheckoutController::class, 'posCheckout'])->name('cart.checkout.pos');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

Route::get('/products/cards', [ProductController::class, 'cards'])->name('products.cards');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
// web.php
Route::get('/product/{product}', [ProductController::class, 'showProductWithCart'])->name('product.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/select-store', [CartController::class, 'selectStorePage'])->name('cart.selectStore');


Route::resource('stores', StoreController::class);

// Route to display the form to create a new inventory record (GET request)
Route::get('/store-inventories/create', [StoreInventoryController::class, 'create'])->name('store-inventories.create');

// Route to handle the form submission (POST request)
Route::post('/store-inventories', [StoreInventoryController::class, 'store'])->name('store-inventories.store');

Route::resource('products', ProductController::class);
// Show product details
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('/', function () {
    return view('welcome');
});




use App\Http\Controllers\Admin\SettingsController;

// In routes/web.php
Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
Route::post('/admin/settings/update', [SettingsController::class, 'update'])->name('admin.settings.update');
