<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Mail\AccountInformationMail;  
use Mail;   
use Carbon\Carbon;


class AuthenticationController extends Controller
{

    // public function Email()
    // {
    //     return (
    //         view('email-page')
    //     );
    // }

    public function loginAuthentication(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'username' => 'required|alpha_num|min:8',
                'password' => 'required|string|min:8',
            ]);
    
            // if (!Auth::attempt($validatedData)){
            //     return response()->json([
            //         'message' => 'Invalid Credentials',
            //         'error' => 'Invalid username or password',
            //     ], 401);
            // }
    
            // $user = Auth::user();
            $user = Account::where('username', $validatedData['username'])->first();
            if($user && Hash::check($validatedData['password'], $user->password)){
                $token = $user->createToken('Personal Access Token')->plainTextToken;
                $cookie = cookie('jwt', $token, minutes: 60 * 24); # domain:'proptrack-capstone-system.vercel.app', secure: true
                return response()->json([
                    'message' => 'Login Successful',
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
                    'token' => $token, 
                    // 'message' => 'Login Successfully',
                ])->withCookie($cookie); 
            }

            return response()->json([
                'message' => 'Invalid Credentials',
                'error' => 'Invalid username or password',
            ], 401);
            


        }catch (\Exception $e) {
            return response()->json([
            'message' => 'Failed to Login. Please try again later.',
            'error' => $e->getMessage()
            ], 500);
        }
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
