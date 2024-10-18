<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\RentalAgreement;
use App\Models\RentedUnitDetails;
use App\Models\Apartment;
use App\Models\BoardingHouse;

class RentalController extends Controller
{
    public function Show_Tenant_Information($unitId)
    {
        try{
            $info = RentalAgreement::with('tenant')->where('rented_unit_id', $unitId)->first();
            return response()->json([
                'message' => 'Success',
                'data' => $info,
            ], 201);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }
    // public function Show_Tenant_Information($unitId, $propertyType)
    // {
    //     if (!is_numeric($unitId) || $unitId <= 0) {
    //         return response()->json(['message' => 'Invalid unit ID'], 400);
    //     }
    
    //     try {
    //         // Start with fetching the rental agreements
    //         $query = RentalAgreement::with('tenant', 'rentedUnitDetails');
    
    //         if ($propertyType == 'boarding house') {
    //             // Retrieve all rental agreements for the given unit ID
    //             $agreements = $query->where([
    //                 ['rented_unit_id', $unitId],
    //                 ['rented_unit_type', $propertyType]
    //             ])->get(); // Use get() to fetch all agreements
    
    //             if ($agreements->isEmpty()) {
    //                 return response()->json(['message' => 'No agreements found for the specified unit'], 404);
    //             }
    
    //             // Return all agreements and their associated tenants and rented unit details
    //             return response()->json([
    //                 'message' => 'Success',
    //                 'data' => $agreements,
    //             ], 200); // Use 200 for successful response
    //         } else {
    //             // Handle other property types if needed
    //             $agreements = $query->where([
    //                 ['rented_unit_id', $unitId],
    //                 ['rented_unit_type', $propertyType]
    //             ])->get();
    
    //             if ($agreements->isEmpty()) {
    //                 return response()->json(['message' => 'No agreements found for the specified unit'], 404);
    //             }
    
    //             return response()->json([
    //                 'message' => 'Success',
    //                 'data' => $agreements,
    //             ], 200);
    //         }
    
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
    //     }
    // }
    


    public function Show_Payment($tenantId)
    {
        try{
            $payment = RentalAgreement::with('tenant')->where('tenant_id', $tenantId)->first();
            return response()->json([
                'message' => 'Success',
                'data' => $payment,
            ], 201);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }


    public function Tenant_list() #for account page
    {
        try {
            $tenantList = RentalAgreement::with(['tenant', 'rentedUnit'])->get();


            return response()->json([
                'message' => 'Query Data Success',
                'data' => $tenantList,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Query failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function OccupiedBed()
    {
        try {
            $tenant = RentedUnitDetails::with('rentalagreement.tenant')
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query failed', 'error' => $e->getMessage()], 500);
        }
    }

    

  

    


    
}
