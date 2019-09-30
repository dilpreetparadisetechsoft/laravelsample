<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('estimate/invoice/pdf/view/{estimate_invoice_id?}/{uuid?}', 'Api\Customer\FinanceController@estimateInvoicePDF');
Route::get('estimate/generate/pdf/{estimate_id?}/{uuid?}', 'Api\Estimate\EstimateController@generatePDF');