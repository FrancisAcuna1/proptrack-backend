<?php

namespace App\Http\Controllers;
use Illuminate\Notifications\Notifiable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use App\Models\Account;
use App\Models\RentalAgreement;
use App\Models\Apartment;
use App\Models\BoardingHouse;
use App\Models\MaintenanceRequest;
use App\Models\ProfileImage;
use Carbon\Carbon;

class UserController extends Controller
{
    public function email()
    {
        return view('email-page');
    }
    public function Tenant_Information_lease($id)
    {
        try{
            $assessmentInfo = RentalAgreement::with(['tenant', 'rentedUnit'])->where('tenant_id',  $id)->get();


            return response()->json([
                'message' => 'Query Data Success',
                'data' => $assessmentInfo,
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query failed', 'error' => $e->getMessage()], 500);
        }
    }

    Public function Tenant_Information($id)
    {
        
        try{
            $tenantInfo = Account::find($id);
            return response()->json([
                'message' => 'Query Data Success',
                'data' => $tenantInfo
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query failed', 'error' => $e->getMessage()], 500);
        }
        
    }

    Public function Add_Profile_Image(Request $request)
    {
        try{
            $validatedData =  $request->validate([
                'tenant_id' => 'required|integer',
                'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);
            if ($request->hasFile('profile_image')) {
                $imageFile = $request->file('profile_image');
                $fileName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();

                // Move the file to the desired directory in the public folder
                $imageFile->move(public_path('ProfileImages'), $fileName);

                // Check if the tenant already has a profile image
                $profileImage = ProfileImage::where('tenant_id', $validatedData['tenant_id'])->first();

                if ($profileImage) {
                    // Delete the old profile image file if it exists
                    $oldImagePath = public_path('ProfileImages/' . $profileImage->image_path);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }

                    // Update the existing profile image record
                    $profileImage->update([
                        'image_path' => $fileName,
                    ]);
                } else {
                    // Create a new profile image record
                    ProfileImage::create([
                        'image_path' => $fileName,
                        'tenant_id' => $validatedData['tenant_id'],
                    ]);
                }
                return response()->json([
                    'message' => 'Profile image uploaded successfully.',
                    'image_path' => 'ProfileImages/' . $fileName, // Return the file path for frontend usage
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No profile image uploaded.',
                ], 400);
            }            
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed To Upload Profile Image', 'error' => $e->getMessage()], 500);
        }
    }

    public function Profile_Image($id)
    {   
        try{
            $profile = ProfileImage::Where('tenant_Id', $id)->first();
            if(!$profile){
                return response()->json([
                    'message' => 'No profile image found!',
                ], 404);
            }

            return response()->json([
                'message' => 'Successfully retrieve the image',
                'data' => $profile,
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed To Retrieve Profile Image', 'error' => $e->getMessage()], 500);
        }
    }

    public function Change_Username(Request $request){
        try{
            $validatedData = $request->validate([
                'new_username' => 'required|alpha_num|min:8|unique:users,username', // Ensure the username is unique
            ]);
    
            // Get the authenticated user
            $user = auth()->user();
    
            // Update the username
            $user->username = $validatedData['new_username'];
            $user->save();
    
            return response()->json([
                'message' => 'Username changed successfully.',
                'username' => $user->username, // Return the new username
            ], 200);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed To Change Username', 'error' => $e->getMessage()], 500);
        }
    }

}
