<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Account;
use App\Models\RentalAgreement;
use App\Models\Apartment;
use App\Models\BoardingHouse;


class UserController extends Controller
{
    public function Tenant_Assement_Fee($id)
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
}
