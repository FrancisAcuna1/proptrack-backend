<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Account;
use App\Models\RentalAgreement;
use App\Models\RentedUnitDetails;
use App\Models\Bed;
use App\Models\Apartment;
use App\Models\BoardingHouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Mail\AccountInformationMail;  
use Mail;   
use Carbon\Carbon;


class AuthenticationController extends Controller
{

    public function Email()
    {
        return (
            view('email-page')
        );
    }
    public function Apartment_Tenant_Registration(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'firstname' => 'required|string|max:25',
                'middlename' => 'nullable|string|max:16',
                'lastname' => 'required|string|max:16',
                'contact' => ['required', 'regex:/^(09|\+639)\d{9}$/'],
                'email' => 'required|email',
                // 'email' => 'required|email|unique:users,email',
                'username' => 'required|alpha_num|unique:users,username|min:8',
                'password' => 'required|alpha_num|min:8',
                'user_type' => 'required|string',
                'street' => 'required|string',
                'barangay' => 'required|string',
                'municipality' => 'required|string',
                'rentalfee' => 'required|integer|min:1',
                'deposit' => 'required|integer|min:1',
                'startDate' => 'required|date_format:m/d/Y',
                'endDate' => 'required|date_format:m/d/Y|after_or_equal:startDate',
                'rented_unit_id' => 'required|integer|min:1',
                'rented_unit_type' => 'required|string',
                'Newstatus' => 'required|string',
                'roomid' => 'required|integer',
                'rented_bed_number' => 'required|integer'
            ]);

            $data = [
                'firstname' => $validatedData['firstname'],
                'middlename' => $validatedData['middlename'],
                'lastname' => $validatedData['lastname'],
                'contact' => $validatedData['contact'],
                'email'=> $validatedData['email'],
                'username' => $validatedData['username'],
                'password' => Hash::make($validatedData['password']),
                'user_type' => $validatedData['user_type'],
                'street' => $validatedData['street'],
                'barangay' => $validatedData['barangay'],
                'municipality' => $validatedData['municipality'],
            ];

            $tenant = Account::create($data);


            $rentalAgreement = RentalAgreement::create([
                'tenant_id' => $tenant->id,
                'rented_unit_id' => $validatedData['rented_unit_id'],
                'rented_unit_type' => $validatedData['rented_unit_type'],
                'rental_fee' => $validatedData['rentalfee'],
                'deposit' => $validatedData['deposit'],
                'lease_start_date' => Carbon::createFromFormat('m/d/Y', $validatedData['startDate'])->format('Y-m-d'),
                'lease_end_date' => Carbon::createFromFormat('m/d/Y', $validatedData['endDate'])->format('Y-m-d'),
            ]);

            $rendeUnitDetails = RentedUnitDetails::create([
                'rental_agreement_id' => $rentalAgreement->id,
                'room_id' => $validatedData['roomid'],
                'rented_bed_number' => $validatedData['rented_bed_number'],
            ]);

            if($validatedData['rented_unit_type'] === "Apartment"){
                $apartment = Apartment::find($validatedData['rented_unit_id']);
                if ($apartment) {
                    $apartment->status = $validatedData['Newstatus'];
                    $apartment->save();
                }
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
                'data' => $tenant, $rentalAgreement, $rendeUnitDetails, $bed
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Boardinghouse_Tenant_Registration(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'firstname' => 'required|string|max:25',
                'middlename' => 'nullable|string|max:16',
                'lastname' => 'required|string|max:16',
                'contact' => ['required', 'regex:/^(09|\+639)\d{9}$/'],
                'email' => 'required|email',
                // 'email' => 'required|email|unique:users,email',
                'username' => 'required|alpha_num|unique:users,username|min:8',
                'password' => 'required|alpha_num|min:8',
                'user_type' => 'required|string',
                'street' => 'required|string',
                'barangay' => 'required|string',
                'municipality' => 'required|string',
                'rentalfee' => 'required|integer|min:1',
                'deposit' => 'required|integer|min:1',
                'startDate' => 'required|date_format:m/d/Y',
                'endDate' => 'required|date_format:m/d/Y|after_or_equal:startDate',
                'rented_unit_id' => 'required|integer|min:1',
                'rented_unit_type' => 'required|string',
                'Newstatus' => 'required|string',
                'roomid' => 'required|integer',
                'rented_bed_number' => 'required|integer'
            ]);

            $data = [
                'firstname' => $validatedData['firstname'],
                'middlename' => $validatedData['middlename'],
                'lastname' => $validatedData['lastname'],
                'contact' => $validatedData['contact'],
                'email'=> $validatedData['email'],
                'username' => $validatedData['username'],
                'password' => Hash::make($validatedData['password']),
                'user_type' => $validatedData['user_type'],
                'street' => $validatedData['street'],
                'barangay' => $validatedData['barangay'],
                'municipality' => $validatedData['municipality'],
            ];

            $tenant = Account::create($data);

            $rentalAgreement = RentalAgreement::create([
                'tenant_id' => $tenant->id,
                'rented_unit_id' => $validatedData['rented_unit_id'],
                'rented_unit_type' => $validatedData['rented_unit_type'],
                'rental_fee' => $validatedData['rentalfee'],
                'deposit' => $validatedData['deposit'],
                'lease_start_date' => Carbon::createFromFormat('m/d/Y', $validatedData['startDate'])->format('Y-m-d'),
                'lease_end_date' => Carbon::createFromFormat('m/d/Y', $validatedData['endDate'])->format('Y-m-d'),
            ]);

            $rendeUnitDetails = RentedUnitDetails::create([
                'rental_agreement_id' => $rentalAgreement->id,
                'room_id' => $validatedData['roomid'],
                'rented_bed_number' => $validatedData['rented_bed_number'],
            ]);

            if($validatedData['rented_unit_type'] === "Boarding House"){
                $boardinghouse = BoardingHouse::find($validatedData['rented_unit_id']);
                if ($boardinghouse) {
                    $bed = Bed::where('room_id', $validatedData['roomid'])
                    ->where('bed_number', $validatedData['rented_bed_number'])
                    ->first();

                    if ($bed) {
                        $bed->status = 'Occupied'; // Assuming 'Occupied' is the status indicating the bed is taken
                        $bed->save();
                    }

                    $allBedsOccupied = Bed::where('room_id', $validatedData['roomid'])
                    ->where('status', '!=', 'Occupied')
                    ->doesntExist();

                    if($allBedsOccupied){
                        $boardinghouse->status = 'Occupied';
                        $boardinghouse->save();
                    }else{
                        $boardinghouse->status = $validatedData['Newstatus'];
                        $boardinghouse->save();
                    }     
                }
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
                'data' => $tenant, $rentalAgreement, $rendeUnitDetails, $bed
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

            $updateTenant = Account::find($id);

            if(!$updateTenant){
                return response()->json(['message' => 'Tenant Not Found'], 404);
            }

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
            return response()->json(['message' => 'Query failed', 'error' => $e->getMessage()], 500);
        }
    }


    public function loginAuthentication(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|alpha_num|min:8',
            'password' => 'required|string|min:8',
        ]);

        if (!Auth::attempt($validatedData)){
            return response()->json([
                'message' => 'Invalid Credentials',
                'error' => 'Invalid username or password',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('Personal Access Token')->plainTextToken;
        $cookie = cookie('jwt', $token, minutes: 60 * 24);
        return response()->json([
            'id' => $user->id,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'middlename' => $user->middlename,
            'age' => $user->age,
            'contact' => $user->contact,
            'street' => $user->street,
            'barangay' => $user->barangay,
            'municipality' => $user->municipality,
            'user_type' => $user->user_type,
            'token' => $token, // Return the
            // 'message' => 'Login Successfully',

            
            
        ])->withCookie($cookie); 
    }

    public function user()
    {
        return Auth::user();
        return response()->json([
            'id' => $user->id,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'lastname' => $user->middlename,
            'username' => $user->username,
            'user_type' => $user->user_type,
        ]);
    }

    public function logout(Request $request)
    {
        try {
        // Delete the current token if you're using Laravel Sanctum
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        // Clear the jwt cookie
        $cookie = Cookie::forget('jwt');

        return response()->json([
            'message' => 'Logged out successfully',
        ])->withCookie($cookie);

        } catch (\Exception $e) {
            return response()->json([
            'message' => 'Logout failed. Please try again later.',
            'error' => $e->getMessage()
            ], 500);
        }
    }

}
