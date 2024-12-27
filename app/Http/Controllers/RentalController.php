<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\RentalAgreement;
use App\Models\RentedUnitDetails;
use App\Models\Apartment;
use App\Models\BoardingHouse;
use App\Models\Bed;
use App\Models\PaymentTransactions;

class RentalController extends Controller
{
    public function Show_Tenant_Information($unitId, $propertyType) #for apartment occupancy of tenant information
    {
        try{
            $info = RentalAgreement::with('tenant')
            ->where('rented_unit_type', $propertyType)
            ->where('rented_unit_id', $unitId)->first();
            return response()->json([
                'message' => 'Success',
                'data' => $info,
            ], 201);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Retrive Tenant Information', 'error' => $e->getMessage()], 500);
        }
    }
    
    // this code is to show the payment of tenant history
    public function Show_Payment($tenantId) 
    {
        try{
            $payment = PaymentTransactions::with('tenant.rentalAgreement')->where('tenant_id', $tenantId)->get();

            return response()->json([
                'message' => 'Success',
                'data' => $payment,
            ], 201);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Retrive Payment History', 'error' => $e->getMessage()], 500);
        }
    }

    public function Filter_Payment_History($tenantId, $category)
    {
        try{
            $filter = PaymentTransactions::with('tenant.rentalAgreement')
            ->where('tenant_id', $tenantId)
            ->where('transaction_type', $category)
            ->get();

            return response()->json([
                'message' => 'Success',
                'data' => $filter,
            ], 201);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }


    public function Tenant_list() #for account information page
    {
        try {
            $tenantList = Account::with(['rentalAgreement.rentedUnit'])->where('user_type', 'User')->get();
            return response()->json([
                'message' => 'Query Data Success',
                'data' => $tenantList,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Query failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Filter_Tenant_List($category)
    {
        try{
            $validStatuses = ['Former', 'Active'];
            if (!in_array($category, $validStatuses)) {
                return response()->json([
                    'message' => 'Invalid status',
                ], 400);
            }

            $filter = Account::with(['rentalAgreement.rentedUnit'])->where('user_type', 'User')
            ->where('status', $category)
            ->get();

            if(!$filter){
                return response()->json([
                    'message' => 'No data found',
                ], 404);
            }
            return response()->json([
                'message' => 'Success to filter tenant list',
                'data' => $filter,
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed To Retrieve Tenant Information', 'error' => $e->getMessage()], 500);
        }
    }

    public function Delete_Tenant_Information($id)
    {
        try{
            $delete = Account::find($id);
            if (!$delete) {
                return response()->json(['message' => 'No tenant information found!'], 404);
            }
            $delete->delete();
            return response()->json([
                'message' => 'Tenant information deleted successfully!',
                'data' => $delete
            ], 200);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed To Delete Tenant Information', 'error' => $e->getMessage()], 500);
        }
    }

    public function OccupiedBed_information($unitId, $propertyType) #for Fully Occupied BH. -the id is for (bh or Unit ID)
    {
        try {
            $OccupiedBedInfo = RentedUnitDetails::with('rentalAgreement.tenant', 'rentedroom.beds')
            ->whereHas('rentalAgreement', function ($query) use ($unitId, $propertyType) {
                $query->where('rented_unit_type', $propertyType)
                    ->where('rented_unit_id', $unitId);
            })
            ->get();

        if ($OccupiedBedInfo->isEmpty()) {
            return response()->json(['message' => 'No Data Found'], 200);
        }

            return response()->json([
                'message' => 'Query Data Success',
                'data' => $OccupiedBedInfo,
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Query failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Remove_Tenant_Occupany($tenantId)
    {   
        try {
            $rentalAgreement = RentalAgreement::with('tenant', 'rentedUnitDetails.rentedroom.beds')->where('tenant_id', $tenantId)->first();

            // if(!$rentalAgreement){
            //     return response()->json(['message' => 'Tenant not found'], 404);
            // }

            if($rentalAgreement->tenant){
                $tenant = Account::where('id', $rentalAgreement->tenant->id)->first();
                if($tenant){
                    $tenant->status = 'Former';
                    $tenant->username = '';
                    $tenant->password = '';
                    $tenant->save();
                }
            }

            if($rentalAgreement->rentedUnitDetails){
                $bed = Bed::where('room_id', $rentalAgreement->rentedUnitDetails->room_id)
                ->where('id', $rentalAgreement->rentedUnitDetails->bed_id)
                ->first();

                if ($bed) {
                    $bed->status = 'Available';
                    $bed->save(); 
                }
            }

            if ($rentalAgreement->rented_unit_type === 'Boarding House') {
                $boardingHouse = BoardingHouse::find($rentalAgreement->rented_unit_id);
                if ($boardingHouse) {
                    $occupiedBeds = Bed::whereHas('rooms', function ($query) use ($boardingHouse) {
                        $query->where('boardinghouse_id', $boardingHouse->id);
                    })->where('status', 'Occupied')->count();
    
                    // Update boarding house status based on occupied beds
                    $boardingHouse->status = ($occupiedBeds === 0) ? 'Available' : 'Occupied';
                    $boardingHouse->save();
                }
            }else if($rentalAgreement->rented_unit_type === 'Apartment'){
                $apartment = Apartment::find($rentalAgreement->rented_unit_id);
                if($apartment){
                    $apartment->status = 'Available';
                }
                $apartment->save();
            }

            // Delete related records
            if ($rentalAgreement->rentedUnitDetails) {
                $rentalAgreement->rentedUnitDetails->delete();
            }
            $rentalAgreement->delete();

            // Delete tenant records
            // if($rentalAgreement->tenant){
            //     $rentalAgreement->tenant->delete();
            // }
            return response()->json([
                'message' => 'Tenant occupancy removed successfully',

            ], 200);
            

        }catch (\Exception $e) {
            return response()->json(['message' => 'Removing Tenant failed', 'error' => $e->getMessage()], 500);
        }
    }


    public function Tenant_Occupancy_Information($id, $tenantId) #this function is for tenant information who occupied BH and Room
    {
        try{
            $tenantOccupancyInfo = RentalAgreement::with([
                'tenant',
                'rentedUnitDetails.rentedroom.beds' => function ($query) {
                    $query->where('status', 'Occupied');
                }
            ])
            ->where('rented_unit_id', $id)
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(function ($rentalAgreement) {
                if ($rentalAgreement->rentedUnitDetails && $rentalAgreement->rentedUnitDetails->rentedroom) {
                    $rentalAgreement->rentedUnitDetails->rentedroom->beds = 
                        $rentalAgreement->rentedUnitDetails->rentedroom->beds
                        ->where('bed_number', $rentalAgreement->rentedUnitDetails->rented_bed_number)
                        ->values();
                }
                return $rentalAgreement;
            });
    
            return response()->json([
                'message' => 'Query Data Success',
                'data' => $tenantOccupancyInfo,
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }


    public function Tenant_Payment_Info($tenantId)
    {
        try{
            $payment = PaymentTransactions::where('tenant_id', $tenantId)
            ->whereIn('transaction_type', ['Advance Payment', 'Rental Fee', 'Initial Payment']) // i put this to get the rental fee ralated
            ->get();
            
            if(!$payment){
                return response()->json(['message' => 'No Payment found for this tenant'], 404);
            }

            return response()->json([
                'message' => 'Query Data Success',
                'data' => $payment,
            ]);


        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }

    Public function Tenant_Security_Deposit($tenantId)
    {
        try{
            $securityDeposit = RentalAgreement::with('tenant')
            ->where('tenant_id', $tenantId)
            ->select('deposit', 'is_last_month', 'tenant_id')
            ->first();

            if(!$securityDeposit){
                return response()->json([
                    'message' => 'No Security Desposit Found!'
                ], 404);
            }

            return response()->json([
                'message' => 'Security Deposit Found!',
                'data' => $securityDeposit,
            ]);


        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed Retrieve Security Deposit', 'error' => $e->getMessage()], 500);
        }
    }
    
}
