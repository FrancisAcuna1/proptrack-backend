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
            $allApartment = Apartment::with('inclusions.inclusion')->get();
            $allBoardinghouse = BoardingHouse::with('rooms', 'inclusions.inclusion')->get();
            
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
            $allApartment = Apartment::with('inclusions.inclusion')->get();
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
            $boardinghouse = Property::with(['boardingHouses' ])->get();
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
    
}
