<?php


use Illuminate\Http\Request;
use App\Services\QuickBooksServices;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use QuickBooksOnline\API\Facades\Customer;


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

Route::get('/', function (Request $request) {
    $request->session()->forget('inovice');
    return view('welcome');
});


Route::view('/test','test')->name('home');
Route::get('quick-books/authorize',function() {
    $url = app('QuickBooksServices')->getAuthUrl();

    // return  $url;
    
    return \Redirect::to($url);


})->name('quick-books.authorize');

Route::get('quick-books/callback',function(Request $request) {

    // Session::set('token');
   return app('QuickBooksServices')->getToken($request->getQueryString());
});

Route::get('create=customer',function() {
   return  app('QuickBooksServices')->createCustomer();


    
})->name('quickbooks.customers.create');

Route::get('all-customers',function() {

 
    $customers = [];
    return view("customers",compact('customers'));
})->name('quickbooks.customers.all');


Route::get('create-invoice',function() {
    return  app('QuickBooksServices')->createInvoice();

})->name('quickbooks.create-invoice');

Route::get('create-item',function() {
    return  app('QuickBooksServices')->createItem();
})->name('quickbooks.create-item');

Route::get('create-account',function(){
    return  app('QuickBooksServices')->createAccount();
})->name('create-account');

Route::get('send-invoice',function() {
    return  app('QuickBooksServices')->sendInvoice();
})->name('send-invoice');

Route::post('/hook',function(Request $request) {
    \Log::info($request);
});