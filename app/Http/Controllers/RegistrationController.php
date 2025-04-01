<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log; // Import Log at the top
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Account;
use App\Models\RentalAgreement;
use App\Models\RentedUnitDetails;
use App\Models\Bed;
use App\Models\Room;
use App\Models\Apartment;
use App\Models\BoardingHouse;
use App\Models\PaymentTransactions;
use App\Models\Revenue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Mail\AccountInformationMail;
use App\Http\Requests\UserRegistrationRequest;
use App\Http\Requests\ApartmentUserRegistration;    
use Mail;   
use Carbon\Carbon;


class RegistrationController extends Controller
{
    
    // public function Email()
    // {
    //     return (
    //         view('email-page')
    //     );
    // }
    
    public function Apartment_Tenant_Registration(ApartmentUserRegistration $request)
    {
        try {
            $validatedData = $request->validated();

            $totalAmount = 0;
            
            $existingTenant = Account::where('firstname', $validatedData['firstname'])
            ->where('lastname', $validatedData['lastname'])
            ->first();

            if($existingTenant){
                if($existingTenant->status === 'Active'){
                    return response()->json([
                        'message' => 'Tenant is already active and cannot be registered again.'
                    ], 400);
                }
                 // Update existing tenant information
                 $existingTenant->update([
                    'firstname' => $validatedData['firstname'],
                    'middlename' => $validatedData['middlename'],
                    'lastname' => $validatedData['lastname'],
                    'status' => $validatedData['status'],
                    'contact' => $validatedData['contact'],
                    'street' => $validatedData['street'],
                    'barangay' => $validatedData['barangay'],
                    'municipality' => $validatedData['municipality'],
                    'username' => $validatedData['username'],
                    'password' => Hash::make($validatedData['password']),
                ]);

                $tenant = $existingTenant;
            }else{
                $data = [
                    'firstname' => $validatedData['firstname'],
                    'middlename' => $validatedData['middlename'],
                    'lastname' => $validatedData['lastname'],
                    'contact' => $validatedData['contact'],
                    'email'=> $validatedData['email'],
                    'username' => $validatedData['username'],
                    'password' => Hash::make($validatedData['password']),
                    'user_type' => $validatedData['user_type'],
                    'status' => $validatedData['status'],
                    'street' => $validatedData['street'],
                    'barangay' => $validatedData['barangay'],
                    'municipality' => $validatedData['municipality'],
                ];
    
                $tenant = Account::create($data);
            }

            $rentalAgreement = RentalAgreement::create([
                'tenant_id' => $tenant->id,
                'rented_unit_id' => $validatedData['rented_unit_id'],
                'rented_unit_type' => $validatedData['rented_unit_type'],
                'rental_fee' => $validatedData['rentalfee'],
                'initial_payment' => $validatedData['initial_payment'],
                'advance_payment' => empty($validatedData['advancepayment']) ? null : $validatedData['advancepayment'],
                'prepaid_rent_period' => $validatedData['prepaidrentperiod'],
                'deposit' => $validatedData['deposit'],
                'lease_start_date' => Carbon::createFromFormat('m/d/Y', $validatedData['startDate'])->format('Y-m-d'),
                'is_last_month' => false
                // 'lease_end_date' => Carbon::createFromFormat('m/d/Y', $validatedData['endDate'])->format('Y-m-d'),
            ]);

            if($validatedData['rented_unit_type'] === "Apartment"){
                $apartment = Apartment::find($validatedData['rented_unit_id']);
                if ($apartment) {
                    $apartment->status = $validatedData['Newstatus'];
                    $apartment->save();
                }
            }

            $totalAmount = $validatedData['initial_payment'];

             // Create initial payment transaction
            PaymentTransactions::create([
                'tenant_id' => $tenant->id,
                'rented_unit_id' => $validatedData['rented_unit_id'],
                'rented_unit_type' => $validatedData['rented_unit_type'],
                'transaction_type' => 'Initial Payment',
                'amount' => $validatedData['initial_payment'],
                'months_covered' => 1,
                'date' => Carbon::now()->format('Y-m-d'),
                'paid_for_month' => Carbon::now()->format('Y-m-d'),
                'status' => 'Paid',
            ]);

            // Create deposit payment transaction
            $totalAmount += $validatedData['deposit'];
            PaymentTransactions::create([
                'tenant_id' => $tenant->id,
                'rented_unit_id' => $validatedData['rented_unit_id'],
                'rented_unit_type' => $validatedData['rented_unit_type'],
                'transaction_type' => 'Security Deposit',
                'amount' => $validatedData['deposit'],
                'months_covered' => null,
                'date' => Carbon::now()->format('Y-m-d'),
                'paid_for_month' => null,
                'status' => 'Paid',
            ]);
            

            if(!empty($validatedData['advancepayment'])){
                $totalAmount += $validatedData['advancepayment'];
                PaymentTransactions::create([
                    'tenant_id' => $tenant->id,
                    'rented_unit_id' => $validatedData['rented_unit_id'],
                    'rented_unit_type' => $validatedData['rented_unit_type'],
                    'transaction_type' => 'Advance Payment',
                    'amount' => $validatedData['advancepayment'],
                    'months_covered' => empty($validatedData['advancepayment']) ? null : $validatedData['prepaidrentperiod'] - 1,
                    'date' => Carbon::now()->format('Y-m-d'),
                    'paid_for_month' => Carbon::now()->addMonths($validatedData['prepaidrentperiod'] - 1)->format('Y-m-d'),
                    'status' => 'Paid',
                ]);
            }


            // $totalAmount = $validatedData['advancepayment'] + $validatedData['deposit'];

            $paymentMonth = Carbon::parse()->format('m');
            $paymentYear = Carbon::parse()->format('Y');

            $revenue = Revenue::where('month', $paymentMonth)
            ->where('year', $paymentYear)
            ->first();
            
            if($revenue){
                $revenue->total_amount  += $totalAmount;
                $revenue->save();
            }else{
                $revenuee = Revenue::create([
                    'month' => $paymentMonth,
                    'year' => $paymentYear,
                    'total_amount' => $totalAmount,
                ]);
            }

            // if(!$revenue){
            //     $revenuee = Revenue::create([
            //         'month' => $paymentMonth,
            //         'year' => $paymentYear,
            //         'amount' => $validatedData['advancepayment'],
            //     ]);
            // }
                
            // $revenue->total_amount += $validatedData['advancepayment'];
            // $revenue->save();
        
            

            $mailData = [
                'firstname' => $validatedData['firstname'],
                'lastname' => $validatedData['lastname'],
                'username' => $validatedData['username'],
                'password' => $validatedData['password'],
            ];

            Mail::to($validatedData['email'])->send(new AccountInformationMail($mailData));


            return response()->json([
                'message' => 'Tenant Registered Successfully',
                'data' => $tenant,
                'revenue' => $revenue
                // $rentalAgreement, $rendeUnitDetails, $bed
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Boardinghouse_Tenant_Registration(UserRegistrationRequest $request)
    {
        try{
            $validatedData = $request->validated();

            $totalAmount = 0;
            #Check if the tenant already exists
            $existingTenant = Account::where('firstname', $validatedData['firstname'])
            ->where('lastname', $validatedData['lastname'])
            ->first();

            if($existingTenant){
                if($existingTenant->status === 'Active'){
                    return response()->json([
                        'message' => 'Tenant is already active and cannot be registered again.'
                    ], 400);
                }
                // Update existing tenant information
                $existingTenant->update([
                    'firstname' => $validatedData['firstname'],
                    'middlename' => $validatedData['middlename'],
                    'lastname' => $validatedData['lastname'],
                    'status' => $validatedData['status'],
                    'contact' => $validatedData['contact'],
                    'street' => $validatedData['street'],
                    'barangay' => $validatedData['barangay'],
                    'municipality' => $validatedData['municipality'],
                    'username' => $validatedData['username'],
                    'password' => Hash::make($validatedData['password']),
                ]);

                $tenant = $existingTenant;
            }else{
                $data = [
                    'firstname' => $validatedData['firstname'],
                    'middlename' => $validatedData['middlename'],
                    'lastname' => $validatedData['lastname'],
                    'contact' => $validatedData['contact'],
                    'email'=> $validatedData['email'],
                    'username' => $validatedData['username'],
                    'password' => Hash::make($validatedData['password']),
                    'user_type' => $validatedData['user_type'],
                    'status' => $validatedData['status'],
                    'street' => $validatedData['street'],
                    'barangay' => $validatedData['barangay'],
                    'municipality' => $validatedData['municipality'],

                ];
    
                $tenant = Account::create($data);
            }

            $totalAmount = $validatedData['initial_payment'];
             // Create initial payment transaction
            PaymentTransactions::create([
                'tenant_id' => $tenant->id,
                'rented_unit_id' => $validatedData['rented_unit_id'],
                'rented_unit_type' => $validatedData['rented_unit_type'],
                'transaction_type' => 'Initial Payment',
                'amount' => $validatedData['initial_payment'],
                'months_covered' => 1,
                'date' => Carbon::now()->format('Y-m-d'),
                'paid_for_month' => Carbon::now()->format('Y-m-d'),
                'status' => 'Paid',
            ]);

            // Create deposit payment transaction
            $totalAmount += $validatedData['deposit'];
            PaymentTransactions::create([
                'tenant_id' => $tenant->id,
                'rented_unit_id' => $validatedData['rented_unit_id'],
                'rented_unit_type' => $validatedData['rented_unit_type'],
                'transaction_type' => 'Security Deposit',
                'amount' => $validatedData['deposit'],
                'months_covered' => null,
                'date' => Carbon::now()->format('Y-m-d'),
                'paid_for_month' => null,
                'status' => 'Paid',
            ]);
            

            if(!empty($validatedData['advancepayment'])){
                $totalAmount += $validatedData['advancepayment'];
                PaymentTransactions::create([
                    'tenant_id' => $tenant->id,
                    'rented_unit_id' => $validatedData['rented_unit_id'],
                    'rented_unit_type' => $validatedData['rented_unit_type'],
                    'transaction_type' => 'Advance Payment',
                    'amount' => $validatedData['advancepayment'],
                    'months_covered' => empty($validatedData['advancepayment']) ? null : $validatedData['prepaidrentperiod'] - 1,
                    'date' => Carbon::now()->format('Y-m-d'),
                    'paid_for_month' => Carbon::now()->addMonths($validatedData['prepaidrentperiod'] - 1)->format('Y-m-d'),
                    'status' => 'Paid',
                ]);
            }

            // $totalAmount = $validatedData['advancepayment'] + $validatedData['deposit'];

            $paymentMonth = Carbon::parse()->format('m');
            $paymentYear = Carbon::parse()->format('Y');

            $revenue = Revenue::where('month', $paymentMonth)
            ->where('year', $paymentYear)
            ->first();
            
            if($revenue){
                $revenue->total_amount  += $totalAmount;
                $revenue->save();
            }else{
                $revenuee = Revenue::create([
                    'month' => $paymentMonth,
                    'year' => $paymentYear,
                    'total_amount' => $totalAmount,
                ]);
            }

            // $revenue = Revenue::where('month', $paymentMonth)
            // ->where('year', $paymentYear)
            // ->first();

            // if(!$revenue){
            //     $revenuee = Revenue::create([
            //         'month' => $paymentMonth,
            //         'year' => $paymentYear,
            //         'amount' => $validatedData['advancepayment'],
            //     ]);
            // }
                
            // $revenue->total_amount += $validatedData['advancepayment'];
            // $revenue->save();

            $rentalAgreement = RentalAgreement::create([
                'tenant_id' => $tenant->id,
                'rented_unit_id' => $validatedData['rented_unit_id'],
                'rented_unit_type' => $validatedData['rented_unit_type'],
                'rental_fee' => $validatedData['rentalfee'],
                'initial_payment' => $validatedData['initial_payment'],
                'advance_payment' => empty($validatedData['advancepayment']) ? null : $validatedData['advancepayment'],
                'prepaid_rent_period' => $validatedData['prepaidrentperiod'],
                'deposit' => $validatedData['deposit'],
                'lease_start_date' => Carbon::createFromFormat('m/d/Y', $validatedData['startDate'])->format('Y-m-d'),
                'is_last_month' => false
                // 'lease_end_date' => Carbon::createFromFormat('m/d/Y', $validatedData['endDate'])->format('Y-m-d'),
            ]);

            if (!is_array($validatedData['bedId'])) {
                $validatedData['bedId'] = [$validatedData['bedId']];
            }            

            foreach ($validatedData['bedId'] as $bedId) {
                RentedUnitDetails::create([
                    'rental_agreement_id' => $rentalAgreement->id,
                    'room_id' => $validatedData['roomid'],
                    'bed_id' => $bedId,
                ]);
    
                // Update the bed status to "Occupied"
                $bed = Bed::where('room_id', $validatedData['roomid'])->where('id', $bedId)->firstOrFail();
                if (!$bed) {
                    return response()->json(['message' => 'Bed not found'], 404);
                }
                $bed->status = 'Occupied';
                $bed->save();
            }
    
            // Check and update the boarding house status
            if ($validatedData['rented_unit_type'] === "Boarding House") {
                $boardinghouse = BoardingHouse::findOrFail($validatedData['rented_unit_id']);
                
                $allRoomsOccupied = Room::where('boardinghouse_id', $boardinghouse->id)
                    ->whereDoesntHave('beds', function ($query) {
                        $query->where('status', '!=', 'Occupied');
                    })
                    ->count() === Room::where('boardinghouse_id', $boardinghouse->id)->count();
    
                $boardinghouse->status = $allRoomsOccupied ? 'Occupied' : $validatedData['Newstatus'];
                $boardinghouse->save();
            }  

            $mailData = [
                'firstname' => $validatedData['firstname'],
                'lastname' => $validatedData['lastname'],
                'username' => $validatedData['username'],
                'password' => $validatedData['password'],
            ];

            Mail::to($validatedData['email'])->send(new AccountInformationMail($mailData));

            return response()->json([
                'message' => 'Tenant Registered Successfully',
                'data' => $tenant, $rentalAgreement, $bed
            ], 201);


        }catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Edit_Tenant($id)
    {
        try {
            // $edit = RentalAgreement::with(['tenant'])->where('id', $id)->get();
            $edit = Account::where('id', $id)->first();


            return response()->json([
                'message' => 'Query Data Success',
                'data' => $edit,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Query failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Update_Tenant(Request $request, $id)
    {
        try{
            $validatedData = $request->validate([
                'id' => 'required|integer',
                'firstname' => 'required|string|max:25',
                'middlename' => 'nullable|string|max:16',
                'lastname' => 'required|string|max:16',
                'contact' => ['required', 'regex:/^(09|\+639)\d{9}$/'],
                'email' => 'required|email',
                // 'email' => 'required|email|unique:users,email',
                'user_type' => 'required|string',
                'street' => 'required|string',
                'barangay' => 'required|string',
                'municipality' => 'required|string',
            ]);

            Log::info('validated Tenant Data:', $validatedData);

            $updateTenant = Account::find($id);
            if (!$updateTenant) {
                return response()->json(['message' => 'Tenant Not Found'], 404);
            }

            $existingTenant = Account::where('firstname', $validatedData['firstname'])
                ->where('lastname', $validatedData['lastname'])
                ->first();

            // Ensure $existingTenant is not null before accessing id
            if ($existingTenant && $existingTenant->id !== (int) $validatedData['id']) {
                return response()->json(['message' => 'Tenant with the updated name already exists'], 400);
            }



            // if($updateTenant->firstname !== $validatedData['firstname'] || $updateTenant->lastname !== $validatedData['lastname']){
            //     $existingTenant = Account::where('firstname', $validatedData['firstname'])
            //     ->where('lastname', $validatedData['lastname'])
            //     ->first();

            //     if ($existingTenant) {
            //         return response()->json(['message' => 'Tenant with the updated name is already exists'], 400);
            //     }
            // }

            $updateTenant->firstname = $validatedData['firstname'];
            $updateTenant->middlename = $validatedData['middlename'];
            $updateTenant->lastname = $validatedData['lastname'];
            $updateTenant->contact = $validatedData['contact'];
            $updateTenant->email = $validatedData['email'];
            $updateTenant->user_type = $validatedData['user_type'];
            $updateTenant->street = $validatedData['street'];
            $updateTenant->barangay = $validatedData['barangay'];
            $updateTenant->municipality = $validatedData['municipality'];

            $updateTenant->save();
            return response()->json([
                'message' => 'Update Tenant Information Successfully!',
                'data' => $updateTenant,
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Update Tenant Information', 'error' => $e->getMessage()], 500);
        }
    }
    
}
