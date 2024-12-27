<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\BoardingHouse;
use App\Models\Apartment;
use App\Models\Room;
use App\Models\Equipments;
use App\Models\ApartmentInclusion;
use App\Models\BoardingHouseInclusion;
use App\Models\Account;


class WebsiteController extends Controller
{
    // public function All_Property()
    // {
    //     try{
    //         $all = Property::with(['apartments', 'boardingHouses', ])->get();
    //         return response()->json([
    //             'message' => 'Query all property Sucessfully',
    //             'data' => $all,
    //         ]);
    //     }catch (\Exception $e) {
    //         return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
    //     }
    // }

    public function All()
    {
        try{
            $allApartment = Apartment::with('inclusions.equipment','images')->get();
            $allBoardinghouse = BoardingHouse::with('rooms.beds', 'inclusions.equipment', 'images')->get();
            
            return response()->json([
                'message' => 'Query all Property Sucessfully',
                'data' => $allApartment, $allBoardinghouse
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
    }

    public function All_Apartment()
    {
        try{
            // $apartment = Property::with(['apartments' ])->get();
            $allApartment = Apartment::with('inclusions.equipment')->get();
            return response()->json([
                'message' => 'Query all Apartment Successfully',
                'data' => $allApartment,
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
    }

    public function All_BoardingHouse()
    {
        try{
            $boardinghouse = BoardingHouse::with(['rooms.beds', 'inclusions.equipment' ])->get();
            return response()->json([
                'message' => 'Query all Boardinghouse Successfully',
                'data' => $boardinghouse,
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
    }

    public function All_Available($status)
    {
        try{
            $availableApartment = Apartment::where('status', $status)->get();
            $availableBoardinghouse = BoardingHouse::where('status', $status)->get();
            
           

            return response()->json([
                'message' => 'Query all Available Property Successfully',
                'data' => $availableApartment, $availableBoardinghouse
            ], 201);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
    }

    public function All_Occupied($status)
    {
        try{
            $occupiedApartment = Apartment::where('status', $status)->get();
            $occupiedBoardinghouse = BoardingHouse::where('status', $status)->get();

            return response()->json([
                'message' => 'Query all Occupied Property Successfully',
                'data' => $occupiedApartment, $occupiedBoardinghouse,
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
    }

    // Display the Details of boardinghouse and apartment
    public function ApartmentDetails($propertyId, $unitId)
    {
        try {
            $property = Property::findOrFail($propertyId);

            $apartment = $property->apartments()
                ->with(['inclusions.equipment', 'property', 'images'])
                ->where('id', $unitId)
                ->firstOrFail();

            $LandlordContact = Account::where('user_type', 'Landlord')->first();


            // $inclusions = $apartment->inclusions->map(function ($inclusion) {
            //     return [
            //         'id' => $inclusion->id,
            //         'name' => $inclusion->equipment->name,
            //         'quantity' => $inclusion->quantity,
            //     ];
            // });

            return response()->json([
                'message' => 'Unit Property Found Successfully',
                'apartment' => $apartment,
                'landlord' => $LandlordContact,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error querying data', 'error' => $e->getMessage()], 500);
        }
    }

    public function BoardingHouseDetails($propertyId, $unitId)
    {
        try{
            $property = Property::findOrFail($propertyId);
            $boardinghouse = $property->boardingHouses()
                ->with(['inclusions.equipment', 'rooms.beds', 'images'])
                ->where('id', $unitId)->first();
            
            $LandlordContact = Account::where('user_type', 'Landlord')->first();

            return response()->json([
                'message' => 'Successfully Query the Data',
                'boardinghouse' => $boardinghouse,
                'landlord' => $LandlordContact,
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Error querying data', 'error' => $e->getMessage()], 500);
        }
    }
    
}
