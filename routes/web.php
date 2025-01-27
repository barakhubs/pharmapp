<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/receipt/{id}/print', function ($id) {
    $sale = \App\Models\Sale::with('customer')->findOrFail($id);
    $saleItems = \App\Models\SaleItem::where('sale_id', $id)->get();

    return view('receipts.print', compact('sale', 'saleItems'));
})->name('receipt.print');

Route::redirect('/laravel/login', '/login')->name('login');
