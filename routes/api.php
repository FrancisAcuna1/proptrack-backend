<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\http\Controllers\AuthenticationController;
use App\http\Controllers\PropertyController;
use App\http\Controllers\WebsiteController;
use App\http\Controllers\RentalController;
use App\http\Controllers\UserController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/check-auth', function (Request $request) {
//     return $request->user();
// });


Route::get('/email', [AuthenticationController::class, 'Email']);
Route::post('/login', [AuthenticationController::class, 'loginAuthentication'])->name('login');
// Route::get('/all', [WebsiteController::class, 'All_Property']);
Route::get('/all', [WebsiteController::class, 'All']);
Route::get('/all_apartment', [WebsiteController::class, 'All_Apartment']);
Route::get('/all_boardinghouse', [WebsiteController::class, 'All_boardinghouse']);
Route::get('/all_available/{status}', [WebsiteController::class, 'All_Available']);
Route::get('/all_occupied/{status}', [WebsiteController::class, 'All_Occupied']);

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/userdata', [AuthenticationController::class, 'user']); // display user info
    Route::post('/logout', [AuthenticationController::class, 'logout']); // logout
    Route::post('/apartment_tenant_registration', [AuthenticationController::class, 'Apartment_Tenant_Registration']); 
    Route::post('/bh_tenant_registration', [AuthenticationController::class, 'Boardinghouse_Tenant_Registration']); 
    Route::post('/create', [PropertyController::class, 'Create_Property']); // create property or Estate
    Route::get('/property_list', [PropertyController::class, 'Property_list']); // display property or Estate list
    // Route::get('/create_unit', [PropertyController::class, 'Create_unit']);
    Route::post('/store_apartment', [PropertyController::class, 'Store_Apartment']); 
    Route::post('/store_boardinghouse', [PropertyController::class, 'Store_BoardingHouse']);
    Route::post('/store_inclusion', [PropertyController::class, 'Store_Inclusion']);
    Route::get('/inclusion_list', [PropertyController::class, 'Inclusion_list']);
    Route::get('/property/{id}', [PropertyController::class, 'Show_all']); // show all propertyType
    Route::get('/property_address/{id}', [PropertyController::class, 'Property_Address']); // address of property for apartment and boarding house
    Route::get('/property/{id}/all_apartment', [PropertyController::class, 'showAll_Apartment']); // show all Apartment when filter
    Route::get('/property/{id}/all_boardinghouse', [PropertyController::class, 'showAll_BoardingHouse']);
    Route::get('/property/{id}/{status}', [PropertyController::class, 'showAllByStatus']);


    Route::get('/property/{id}/details/{unitId}', [PropertyController::class, 'Show_Details_Apartment']); 
    Route::get('/property/{id}/bhdetails/{unitId}', [PropertyController::class, 'Show_Details_Boardinghouse']); 
    // Route::get('/property/{id}/edit/{unitId}', [PropertyController::class, 'Edit_Data']); // details for apartment
    Route::get('/edit_inclusion/{itemid}', [PropertyController::class, 'Edit_Inclusion']);
    Route::get('edit_property/{itemId}', [PropertyController::class, 'Edit_Property']); 
    Route::get('edit_apartment/{itemId}', [PropertyController::class, 'Edit_Apartment']); 
    Route::get('edit_boardinghouse/{itemId}', [PropertyController::class, 'Edit_Boardinghouse']); 
    Route::put('update_inclusion/{ItemId}', [PropertyController::class, 'Update_Inclusion']);
    Route::put('update_property/{ItemId}', [PropertyController::class, 'Update_Property']); 
    Route::put('update_apartment/{ItemId}', [PropertyController::class, 'Update_Apartment']); 
    Route::put('update_boardinghouse/{ItemId}', [PropertyController::class, 'Update_Boardinghouse']); 
    Route::Delete('delete_inclusion/{ItemId}', [PropertyController::class, 'Delete_Inclusion']); 
    Route::Delete('delete_apartment/{ItemId}', [PropertyController::class, 'Delete_Apartment']); 
    Route::Delete('delete_boardinghouse/{ItemId}', [PropertyController::class, 'Delete_Boardinghouse']); 

    // Tenant Occupied Information
    Route::get('show_tenant_info/{itemId}', [RentalController::class, 'Show_Tenant_Information']);
    Route::get('show_payment/{tenantId}', [RentalController::class, 'Show_Payment']);
    Route::get('tenant_list', [RentalController::class, 'Tenant_list']);
    Route::get('edit_tenant/{tenantId}', [AuthenticationController::class, 'Edit_Tenant']);
    Route::put('update_tenant/{tenantId}', [AuthenticationController::class, 'Update_Tenant']);

    // User Dashboard
    Route::get('tenant_assessment_fee/{tenantId}', [UserController::class, 'Tenant_Assement_Fee']);
    Route::get('tenant_information/{tenantId}', [UserController::class, 'Tenant_Information']);

    
});


