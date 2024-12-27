<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ChangePasswordController extends Controller
{
    public function Generate_Otp(Request $request)
    {
        try{
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed', 
            ]);

            $user = Auth::user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Current password is incorrect.'], 400);
            }
    
            $otp = random_int(100000, 999999);
            $expiresAt = now()->addMinutes(10);

            $generate = OtpVerification::updateOrCreate(
                ['user_id' => $user->id],
                ['otp' => $otp, 'expires_at' => $expiresAt]
            );

            Mail::to($user->email)->send(new \App\Mail\OtpMail($otp));

            return response()->json([
                'message' => 'OTP sent to your email.',
                'data' => $generate
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed To Generate Password', 'error' => $e->getMessage()], 500);
        }
    }

    public function Resend_Otp(Request $request)
    {
        try{
            $user = Auth::user();

            if(!$user){
                return response()->json(['message' => 'User not Authenticated.'], 404);
            }

             // Generate a new OTP
            $otp = random_int(100000, 999999);
            $expiresAt = now()->addMinutes(10);

             // Update or create the OTP record for the user
            $otpRecord = OtpVerification::updateOrCreate(
                ['user_id' => $user->id],
                ['otp' => $otp, 'expires_at' => $expiresAt]
            );

            // Send the OTP via email
            Mail::to($user->email)->send(new \App\Mail\OtpMail($otp));

            return response()->json([
                'message' => 'OTP resent successfully to your email.',
                'data' => $otpRecord
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed To Resend OTP', 'error' => $e->getMessage()], 500);
        }
    }

    

    public function Change_Password_OTP(Request $request)
    {
        try{
            $request->validate([
                'otp' => 'required|numeric',
                'new_password' => 'required|min:8|confirmed',
            ]);
    
            $user = Auth::user();
            $otpRecord = OtpVerification::where('user_id', $user->id)
                ->where('otp', $request->otp)
                ->where('expires_at', '>', now())
                ->first();
    
            if (!$otpRecord) {
                return response()->json(['message' => 'Invalid or expired OTP.'], 400);
            }
    
           
            $user->password = Hash::make($request->new_password);
            $user->save();
    
            $otpRecord->delete();
    
            return response()->json(['message' => 'Password changed successfully.'], 200);
            
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed To Change Password', 'error' => $e->getMessage()], 500);
        }
    }
}
