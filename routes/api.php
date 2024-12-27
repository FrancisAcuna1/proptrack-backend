<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\http\Controllers\AuthenticationController;
use App\http\Controllers\RegistrationController;
use App\http\Controllers\PropertyController;
use App\http\Controllers\WebsiteController;
use App\http\Controllers\RentalController;
use App\http\Controllers\UserController;
use App\http\Controllers\ChangePasswordController;
use App\http\Controllers\DashboardController;
use App\http\Controllers\MaintenanceController;
use App\http\Controllers\RevenueController;
use App\http\Controllers\ExpensesController;
use App\http\Controllers\NotificationsController;
use App\http\Controllers\DeliquentController;
use App\http\Controllers\RecurringExpensesController;
use App\http\Controllers\ChatbotController;
/*
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
Route::post('/login', [AuthenticationController::class, 'loginAuthentication'])->name('login');
Route::get('email', [UserController::class, 'email']);
// Route::get('/all', [WebsiteController::class, 'All_Property']);
Route::get('/all_prop', [WebsiteController::class, 'All']);
Route::get('/all_apartment', [WebsiteController::class, 'All_Apartment']);
Route::get('/all_boardinghouse', [WebsiteController::class, 'All_boardinghouse']);
Route::get('/all_available/{status}', [WebsiteController::class, 'All_Available']);
Route::get('/all_occupied/{status}', [WebsiteController::class, 'All_Occupied']);
Route::get('/apartmentdetails/{id}/{unitId}', [WebsiteController::class, 'ApartmentDetails']);
Route::get('/boardinghousedetails/{id}/{unitId}', [WebsiteController::class, 'BoardingHouseDetails']);

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/userdata', [AuthenticationController::class, 'user']); // display user info
    Route::post('/logout', [AuthenticationController::class, 'logout']); // logout
    
    // Registration Controller
    Route::post('/apartment_tenant_registration', [RegistrationController::class, 'Apartment_Tenant_Registration']); 
    Route::post('/bh_tenant_registration', [RegistrationController::class, 'Boardinghouse_Tenant_Registration']);
    Route::get('edit_tenant/{tenantId}', [RegistrationController::class, 'Edit_Tenant']);
    Route::put('update_tenant/{tenantId}', [RegistrationController::class, 'Update_Tenant']);

    // Property Controller
    Route::post('/create', [PropertyController::class, 'Create_Property']); // create property or Estate
    Route::get('/property_list', [PropertyController::class, 'Property_list']); // display property or Estate list
    // Route::get('/create_unit', [PropertyController::class, 'Create_unit']);
    Route::post('/store_apartment', [PropertyController::class, 'Store_Apartment']); 
    Route::post('/store_boardinghouse', [PropertyController::class, 'Store_BoardingHouse']);
    Route::post('/store_inclusion', [PropertyController::class, 'Store_Inclusion']);
    Route::get('/inclusion_list', [PropertyController::class, 'Inclusion_list']);
    Route::get('/all_property/{id}', [PropertyController::class, 'Show_all']); // show all propertyType
    Route::get('/property_address/{id}', [PropertyController::class, 'Property_Address']); // address of property for apartment and boarding house
    Route::get('/property/{id}/all_apartment', [PropertyController::class, 'ShowAll_Apartment']); // show all Apartment when filter
    Route::get('/property/{id}/all_boardinghouse', [PropertyController::class, 'ShowAll_BoardingHouse']);
    Route::get('/property/{id}/{status}', [PropertyController::class, 'ShowAllByStatus']);

    Route::get('/property/{id}/details/{unitId}', [PropertyController::class, 'Show_Apartment_Details']); 
    Route::get('/property/{id}/bhdetails/{unitId}', [PropertyController::class, 'Show_BoardingHouse_Details']); 
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
    Route::get('show_tenant_info/{itemId}/{propType}', [RentalController::class, 'Show_Tenant_Information']);
    Route::get('show_payment/{tenantId}', [RentalController::class, 'Show_Payment']);
    Route::get('filter_payment_history/{tenantId}/{category}', [RentalController::class, 'Filter_Payment_History']);
    Route::get('tenant_list', [RentalController::class, 'Tenant_list']);
    Route::get('filter_tenant_list/{category}', [RentalController::class, 'Filter_Tenant_List']);
    Route::delete('delete_tenant_information/{id}', [RentalController::class, 'Delete_Tenant_Information']);
    Route::get('occupied_bed_info/{itemId}/type/{propType}', [RentalController::class, 'OccupiedBed_information']); #all rental information for boarding house 
    Route::get('tenant_occupancy_info/{itemId}/{tenantId}', [RentalController::class, 'Tenant_Occupancy_Information']); #specific tenant info for boarding house 
    Route::get('tenant_payment/{tenantId}', [RentalController::class, 'Tenant_Payment_Info']); #get the payment information of tenant
    Route::Delete('remove_tenant_occupancy/{tenantId}', [RentalController::class, 'Remove_Tenant_Occupany']); #Remove Tenant Occupancy
    Route::get('security_deposit/{tenantId}', [RentalController::class, 'Tenant_Security_Deposit']); #get the security deposit for lastmonth payment


    // Maintenance Controller
    Route::get('tenant_unit_info/{tenantId}', [MaintenanceController::class, 'Tenant_Unit_Information']);
    Route::post('requestmaintenance', [MaintenanceController::class, 'Request_Maintenance']); // maitenance module
    Route::get('maintenance_request_list', [MaintenanceController::class, 'Maintenance_Request_List']);
    Route::put('accept_maintenance/{itemId}', [MaintenanceController::class, 'Accept_Maintenance_Request']);
    Route::put('rejected_maintenance/{itemId}', [MaintenanceController::class, 'Reject_Maintenance_Request']);
    Route::put('cancel_request/{itemId}', [MaintenanceController::class, 'Cancel_Request']);
    Route::get('edit_request/{itemId}', [MaintenanceController::class, 'Edit_Request']);
    Route::put('update_request/{itemId}', [MaintenanceController::class, 'Update_Request']);
    Route::get('view_request/{itemId}', [MaintenanceController::class, 'ViewRequest']);
    Route::get('get_accepted', [MaintenanceController::class, 'Get_Accepted_Request']);
    Route::get('edit_schedule/{itemId}', [MaintenanceController::class, 'Edit_Schedule']);
    Route::post('add_schedule', [MaintenanceController::class, 'Add_Schedule']);
    Route::get('get_schedule', [MaintenanceController::class, 'Get_Schedule_Maintenance']);
    Route::put('update_schedule/{itemId}', [MaintenanceController::class, 'Update_Schedule']); 
    Route::delete('delete_schedule/{itemId}', [MaintenanceController::class, 'Delete_Schedule']); 
    Route::get('get_status', [MaintenanceController::class, 'Get_Maintenance_Status']);
    Route::get('count_status', [MaintenanceController::class, 'Count_Status']); // this api was count the status of maintenance
    Route::get('filter_status/{category}', [MaintenanceController::class, 'Filter_Status']); // this api is for filter Status
    Route::get('filter_maintenance/{category}/{tenant?}', [MaintenanceController::class, 'Filter_Maintenance_Request']); // this api is for filter Maintenance request
    Route::get('requested_maintenance_list/{tenantId}', [MaintenanceController::class, 'Requested_Maintenance_List']); #this api for user page
    Route::get('view_accepted_request/{itemId}', [MaintenanceController::class, 'View_Accepted_Details']); #this api for user page

    // Revenue Controller
    Route::post('paymentdetails', [RevenueController::class, 'Payment_Details']); // this api display all payment with filter by year and months
    Route::get('listoftenants', [RevenueController::class, 'List_of_Tenant']);
    Route::get('get_tenant_payment/{id}/{type}', [RevenueController::class, 'Get_Tenant_Payment_Details']); // this api for tenant latest payment
    Route::get('get_payor_list/{id}/{type}', [RevenueController::class, 'Get_Payor_List']);
    Route::post('storepayment', [RevenueController::class, 'Store_Payment']);
    Route::get('editpayment/{id}', [RevenueController::class, 'Edit_Payment']);
    Route::put('updatepayment/{id}', [RevenueController::class, 'Update_Payment']);
    Route::delete('delete_payment/{id}', [RevenueController::class, 'Delete_Payment']); 
    Route::post('filter_payment/{category}', [RevenueController::class, 'Filter_Payment']); // this api is for filter payment
    Route::post('calculate_income', [RevenueController::class, 'Calculate_Income']);
    Route::post('income_statistic', [RevenueController::class, 'Income_Statistic']);
    Route::post('paid_by_deposit/{id}', [RevenueController::class, 'Paid_by_Deposit']);
  
    
    // Dashboard Controller
    Route::get('index', [DashboardController::class, 'Index']);
    Route::get('all_tenant', [DashboardController::class, 'All_Tenant']);
    Route::post('getIncome', [DashboardController::class, 'GetIncome']);
    Route::post('getExpenses', [DashboardController::class, 'GetExpenses']);
    Route::get('all', [DashboardController::class, 'All']);

    // Expenses Controller
    Route::get('get_all_property', [ExpensesController::class, 'Get_All_Property']);
    Route::get('edit/{id}', [ExpensesController::class, 'Edit']);
    Route::post('store_expenses', [ExpensesController::class, 'Store_Expenses']);
    Route::post('get_all_expenses', [ExpensesController::class, 'Get_All_Expenses']);
    Route::post('filter_expenses/{category}', [ExpensesController::class, 'Filter_Expenses']);
    Route::post('calculate_expenses', [ExpensesController::class, 'Calculate_Expenses']);
    Route::post('expenses_statistic', [ExpensesController::class, 'Expenses_Statistic']);
    Route::put('update_expenses/{id}', [ExpensesController::class, 'Update_Expenses']);
    Route::delete('delete_expenses/{id}', [ExpensesController::class, 'Delete_Expenses']);

    // Recurring Controller
    Route::post('generate_recurring_expenses', [RecurringExpensesController::class, 'Generate_Recurring_Expenses']);
    Route::post('get_recurring_expenses', [RecurringExpensesController::class, 'Get_RecurringExpenses_Details']);
    Route::post('filter_recurring_expenses/{category}/{type}', [RecurringExpensesController::class, 'Filter_Recurring_Expenses']);
    Route::put('markaspaid/{id}', [RecurringExpensesController::class, 'Paid_Recurring_Expenses']);
    Route::get('edit_recurring/{id}', [RecurringExpensesController::class, 'Edit_Recurring']);
    Route::put('update_recurring/{id}', [RecurringExpensesController::class, 'Update_Recurring']);
    Route::delete('delete/{id}', [RecurringExpensesController::class, 'Delete_Recurring_Expenses']);

    // Notification Controller
    Route::get('total_notifications', [NotificationsController::class, 'totalNotifications']);
    Route::get('getnotifications', [NotificationsController::class, 'getUnreadNotifications']);
    Route::post('notifications/{id}/read', [NotificationsController::class, 'markAsRead']);

    // Deliquent Controller
    Route::post('store_delequent', [DeliquentController::class, 'Store_Delequent']);
    Route::get('get_delequent/{id}', [DeliquentController::class, 'Get_Delequent_Details']);

     // User Account Dashboard
    Route::get('tenant_information_lease/{tenantId}', [UserController::class, 'Tenant_Information_lease']);
    Route::get('tenant_information/{tenantId}', [UserController::class, 'Tenant_Information']);
    Route::post('add_profile_image', [UserController::class, 'Add_Profile_Image']);
    Route::get('profile_image/{id}', [UserController::class, 'Profile_Image']);
    Route::put('change_username', [UserController::class, 'Change_Username']);

    //Change Password Controller
    Route::post('generate_otp', [ChangePasswordController::class, 'Generate_Otp']);
    Route::put('change_password', [ChangePasswordController::class, 'Change_Password_OTP']);


    //Chatbot Controller
    Route::post('chatbot/query', [ChatbotController::class, 'Query']);
    Route::get('chatbot/services', [ChatbotController::class, 'Services']);

    // Expenses Controller
    // Route::get('get_all_property', [ExpensesController::class, 'Get_All_Property']); // this api is to get all property to display in form of expenses
    // Route::post('store_expenses', [ExpensesController::class, 'Store_Expenses']);
 //Landlord Dashboard Home Maintenance_Request_List
    // Route::controller(DashboardController::class)->group(function() {
    //     Route::get('index', 'Index');
    //     Route::get('all_tenant', 'All_Tenant');
    //     Route::post('getIncome', 'GetIncome');
    //     Route::post('getExpenses', 'GetExpenses');
    //     Route::get('all', 'All');
    // });

    // Route::controller(ExpensesController::class)->group(function() {
    //     Route::get('get_all_property', 'Get_All_Property');
    //     Route::get('edit/{id}', 'Edit');
    //     Route::post('store_expenses', 'Store_Expenses');
    //     Route::post('get_all_expenses', 'Get_All_Expenses');
    //     Route::post('filter_expenses/{category}', 'Filter_Expenses');
    //     Route::post('calculate_expenses', 'Calculate_Expenses');
    //     Route::post('expenses_statistic','Expenses_Statistic');
    //     Route::put('update_expenses/{id}', 'Update_Expenses');
    //     Route::delete('delete_expenses/{id}', 'Delete_Expenses');
    // });

      // Route::controller(RecurringExpensesController::class)->group(function () {
    //     Route::post('/generate_recurring_expenses', 'Generate_Recurring_Expenses');
    //     Route::post('/get_recurring_expenses', 'Get_RecurringExpenses_Details');
    //     Route::post('/filter_recurring_expenses/{category}/{type}', 'Filter_Recurring_Expenses'); //filter function
    //     Route::put('/markaspaid/{id}','PaidRecurringExpenses');
    //     Route::get('/edit_recurring/{id}', 'Edit_Recurring');
    //     Route::put('/update_recurring/{id}', 'Update_Recurring');
    //     Route::delete('/delete/{id}','Delete_Recurring_Expenses');
    // });

    // Route::controller(DeliquentController::class)->group(function(){
    //     Route::post('/store_delequent', 'Store_Delequent');
    //     Route::get('/get_delequent/{id}', 'Get_Delequent_Details');
    // });

    
});




