<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\BoardingHouse;
use App\Models\Apartment;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Equipments;
use App\Models\ApartmentInclusion;
use App\Models\BoardingHouseInclusion;


use App\Models\Units;
use Illuminate\Support\Facades\Log;

class PropertyController extends Controller
{
    public function Create_Property(Request $request)
    {
        try {

            $existingProperty = Property::where('propertyname',$request->input('propertyname'))
            ->first();

            if($existingProperty){
                return response()->json([
                    'message' => 'Property is Already exist'
                ], 409);
            }else{
                $validateData = $request->validate([
                    'propertyname' => 'required|string|max:255',
                    'barangay' => 'required|string|max:255',
                    'municipality' => 'required|string|max:255',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3050', // Adjust based on your requirements
                ]);
    
                $fileName = null;
    
                if ($request->hasFile('image')) {
                    $fileName = time() . '.' . $request->file('image')->getClientOriginalExtension();
                    $imagePath = 'images/' . $fileName; // Store relative path
                    $request->file('image')->move(public_path('ApartmentImage'), $fileName); // Move to public/images
                }
        
                $data = [
                    'propertyname' => $validateData['propertyname'],
                    'barangay' => $validateData['barangay'],
                    'municipality' => $validateData['municipality'],
                    'image' => $fileName
                ];
    
                
                $property = Property::create($data);
    
                return response()->json([
                    
                    'message' => 'Created property Successfully',
                    'id' => $property->id,
                    'propertyname' => $property->propertyname,
                    'barangay' => $property->barangay,
                    'municipality' => $property->municipality,
                    'image' => $property->image
                ], 200);
            }

            

        } catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    
        
    }

    // Edit Prorperty
    public function Edit_Property($id)
    { 
        try{
            $editInfo = Property::where('id', $id)->first();
            return response()->json([
            'message' => 'Query Edit Successfully Created!',
            'editProperty' => $editInfo,
            ], 200);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    // Update Property
    public function Update_Property(Request $request, $id)
    {
        try{

            $validateData = $request->validate([
                'propertyname' => 'required|string|max:255',
                'street' => 'required|string|max:255',
                'barangay' => 'required|string|max:255',
                'municipality' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3050', // Adjust based on your requirements
            ]);

            $property = Property::find($id);

            if(!$property)
            {
                return response()->json([
                    'message' => 'No Property Found',
                ]);
            }

            if ($request->hasFile('image')) {
                $fileName = time() . '.' . $request->file('image')->getClientOriginalExtension();
                $imagePath = 'images/' . $fileName; // Store relative path
                $request->file('image')->move(public_path('ApartmentImage'), $fileName);
                $property->image = $fileName; // Move to public/images
            }
    
           
            $property->propertyname = $validateData['propertyname'];
            $property->street = $validateData['street'];
            $property->barangay = $validateData['barangay'];
            $property->municipality = $validateData['municipality'];
            // $property->image = $fileName;

            $property->save();

            return response()->json([
                
                'message' => 'Property updated successfully!',
                'id' => $property->id,
                'propertyname' => $property->propertyname,
                'street' => $property->street,
                'barangay' => $property->barangay,
                'municipality' => $property->municipality,
                'image' => $property->image
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Property_list()
    {

        try{
            $property = Property::all();
            return response()->json($property);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
            
    }

    public function Property_Address($id) #this code is for address of property that need for apartment / boardinghouse address
    {
        try{
            $propertyAddress = Property::where('id', $id)->first();
            return response()->json([
                'message' => 'Successfully Query Data',
                'data' => $propertyAddress
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
    }


    // Apartment & Boarding House Creation

    public function Store_Apartment(Request $request)
    {
        $existingApartment = Apartment::where('apartment_name',$request->input('apartmentname'))
        ->where('property_id', $request->input('propertyid'))
        ->first();

        if($existingApartment)
        {
            return response()->json(['message' => 'Apartment already exists'], 409);
        }else{
            try{
                $propertyData = $request->validate([
                    'propertyid' => 'required|integer',
                    'apartmentname' => 'required|string|max:24',
                    'numberofrooms' => 'required|integer',
                    'capacity' => 'required|integer',
                    'rentalfee' => 'required|numeric',
                    'payorname' => 'nullable|string',
                    'status' => 'required|string',
                    'property_type' => 'required|string',
                    'inclusion' => 'required|json',                
                    'buildingno' => 'required|string',
                    'street' => 'required|string|max:16',
                    'barangay' => 'required|string|max:16',
                    'municipality' => 'required|string|max:16',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3050',
                    
                ]);

                
                $fileName = null;
    
                if ($request->hasFile('image')) {
                    $fileName = time() . '.' . $request->file('image')->getClientOriginalExtension();
                    $imagePath = 'images/' . $fileName; // Store relative path
                    $request->file('image')->move(public_path('ApartmentImage'), $fileName); // Move to public/images
                }
    
                $inclusions = json_decode($propertyData['inclusion'], true);
                foreach ($inclusions as &$inclusion) {
                    if (!isset($inclusion['quantity']) || $inclusion['quantity'] < 1) {
                        $inclusion['quantity'] = 1; // Default to 1 if not set or less than 1
                    }
                }
    
                $inclusionNames = implode(', ', array_map(function($inclusion) {
                    return isset($inclusion['name']) ? $inclusion['name'] : 'Unknown Inclusion';
                }, $inclusions));
        
                $data = [
                    'property_id' => $propertyData['propertyid'],
                    'apartment_name' => $propertyData['apartmentname'],
                    'number_of_rooms' => $propertyData['numberofrooms'],
                    'capacity' => $propertyData['capacity'],
                    'rental_fee' => $propertyData['rentalfee'],
                    'payor_name' => $propertyData['payorname'],
                    'status' => $propertyData['status'],
                    'property_type' => $propertyData['property_type'],
                    'building_no' => $propertyData['buildingno'],
                    'street' => $propertyData['street'],
                    'barangay' => $propertyData['barangay'],
                    'municipality' => $propertyData['municipality'],
                    'image' => $fileName,
                ];
    
               
        
                $apartment = Apartment::create($data);
              
                foreach ($inclusions as $inclusion) {
                    ApartmentInclusion::create([
                        'apartment_id' => $apartment->id,
                        'inclusion_id' => $inclusion['id'],
                        'quantity' => $inclusion['quantity']
                    ]);
                }
    
    
                return response()->json([
                        
                    'message' => 'Created Apartment Successfully',
                    'apartment' => $apartment
                ], 200);
    
            }catch (\Exception $e) {
                return response()->json(['message' => 'Created Apartment Failed', 'error' => $e->getMessage()], 500);
            }

        }
            
    }


    public function Store_BoardingHouse(Request $request)
    {
        $existingBoardinghouse = BoardingHouse::where('boarding_house_name',$request->input('boardinghousename'))
        ->where('property_id', $request->input('propertyid'))
        ->first();

        if($existingBoardinghouse){
        
            return response()->json([
                'message' => 'Boarding House is Already Exist'
            ], 409);
        }else{
            try{

                $propertyData = $request->validate([
                    'propertyid' => 'required|integer',
                    'boardinghousename' => 'required|string|max:24',
                    'numberofrooms' => 'required|integer',
                    'capacity' => 'required|integer',
                    'rentalfee' => 'required|numeric',
                    'payorname' => 'nullable|string',
                    'status' => 'required|string',
                    'property_type' => 'required|string',
                    'inclusion' => 'required|json',
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3050',
                    'buildingno' => 'required|string',
                    'street' => 'required|string|max:16',
                    'barangay' => 'required|string|max:16',
                    'municipality' => 'required|string|max:16',
                    'rooms' => 'array|required',
                    'rooms.*.room_number' => 'required|integer',
                    'rooms.*.number_of_beds' => 'required|integer',
                    'rooms.*.beds' => 'array', // Adding beds array
                    'rooms.*.beds.*.bed_number' => 'required|integer',
                    'rooms.*.beds.*.bed_type' => 'required|string', // Assuming bed_type is optional
                    'rooms.*.beds.*.status' => 'required|string', // Assuming status is optional
                ]);

                // \Log::info($request->all());
    
                $fileName = null;
    
                if ($request->hasFile('image')) {
                    $fileName = time() . '.' . $request->file('image')->getClientOriginalExtension();
                    $imagePath = 'images/' . $fileName; // Store relative path
                    $request->file('image')->move(public_path('ApartmentImage'), $fileName); // Move to public/images
                }
                
                $inclusions = json_decode($propertyData['inclusion'], true);
                // \Log::info('Inclusions:', $inclusions); // Add this line
    
                $inclusionNames = implode(', ', array_map(function($inclusion) {
                    return isset($inclusion['name']) ? $inclusion['name'] : 'Unknown Inclusion';
                }, $inclusions));
    
                $data = [
                    'property_id' => $propertyData['propertyid'],
                    'boarding_house_name' => $propertyData['boardinghousename'],
                    'number_of_rooms' => $propertyData['numberofrooms'],
                    'capacity' => $propertyData['capacity'],
                    'rental_fee' => $propertyData['rentalfee'],
                    'payor_name' => $propertyData['payorname'],
                    'status' => $propertyData['status'],
                    'property_type' => $propertyData['property_type'],
                    'image' => $fileName,
                    // 'inclusion' => $propertyData['inclusion'],
                    'building_no' => $propertyData['buildingno'],
                    'street' => $propertyData['street'],
                    'barangay' => $propertyData['barangay'],
                    'municipality' => $propertyData['municipality'],
    
                ];
    
                $boardinghouse = BoardingHouse::create($data);
    
                foreach ($inclusions as $inclusion) {
                    BoardingHouseInclusion::create([
                        'boardinghouse_id' => $boardinghouse->id,
                        'inclusion_id' => $inclusion['id'],
                        'quantity' => $inclusion['quantity']
                    ]);
                }
    
                // $roomsData = array_map(function ($room) use ($boardinghouse) {
                //     return [
                //         'boardinghouse_id' => $boardinghouse->id, // Assign the boarding house ID
                //         'room_number' => $room['room_number'],
                //         'number_of_beds' => $room['number_of_beds'],
                //     ];
                // }, $propertyData['rooms']);  #old code for storing rooms and bed in a room table
                // $boardinghouse->rooms()->createMany($roomsData);
              
        
                // Create the related rooms
                $roomsData = [];
                foreach ($propertyData['rooms'] as $room) {
                    $roomsData[] = [
                        'boardinghouse_id' => $boardinghouse->id, // Assign the boarding house ID
                        'room_number' => $room['room_number'], 
                        'number_of_beds' => $room['number_of_beds'],
                    ];
                }
                $createdRooms = $boardinghouse->rooms()->createMany($roomsData);

                  // Prepare and create beds for each room
                foreach ($propertyData['rooms'] as $index => $room) {
                    foreach ($room['beds'] as $bed) {
                        Bed::create([
                            'room_id' => $createdRooms[$index]->id, // Use the created room's ID
                            'bed_number' => $bed['bed_number'],
                            'bed_type' => $bed['bed_type'] ?? null, // Use null if bed_type is not provided
                            'status' => $bed['status'] ?? null, // Use null if status is not provided
                        ]);
                    }
                }
                
    
    
                return response()->json([
                    'message' => 'Boarding House created successfully!', 
                    'boarding_house' => $boardinghouse
                ]);
    
            }catch (\Exception $e){
                return response()->json(['message' => 'Created Boarding House Failed', 'error' => $e->getMessage()], 500);
            }
        }
        
    }


    // Add Equip or Inclusion
    public function Store_Inclusion(Request $request)
    {
        $existingEquipment =  Equipments::where('name',$request->input('name'))
        ->first();
        if($existingEquipment)
        {
            return response()->json([
                'message' => 'Equipment is Already exist!'
            ], 409);
        }else{
            try{
                $validateData = $request->validate([
                    'name' => 'required|string|max:24|unique:equipments',
                ]);
        
                $data = [
                    'name' => $validateData['name'],
                ];
        
                Equipments::create($data);
        
                return response()->json(['message' => 'Inclusion created successfully!']);
    
            }catch (\Exception $e){
                return response()->json(['message' => 'Created Inclusion Failed', 'error' => $e->getMessage()], 500);
            }
        }
    }

    public function Inclusion_list()    
    {
       
        try{
            $inclusions = Equipments::all();
            return response()->json([
                'message' => 'Inclusion Query successfully Created!',
                'data' => $inclusions
    
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
            
    } 

    public function Edit_Inclusion($id){
        try{
            $item = Equipments::where('id', $id)->first();
            return response()->json([
                'messsage' => 'Edit item Successfully Selected',
                'data' => $item
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
    }

    public function Update_Inclusion(Request $request, $id){
        try{
            $validateData = $request->validate([
                'name' => 'required|string|max:24',
            ]);

            $equipment = Equipments::find($id);

            $equipment->name = $validateData['name'];

            $equipment->save();
            return response()->json([
                'message' => 'Equipment Updated Successfully',
                'update' => $equipment
            ], 201);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Update a Data', 'error' => $e->getMessage()], 500);
        }
    }

    public function Delete_Inclusion($id)
    {
        try{
            $deleteInclusion = Equipments::where('id', $id);
            $deleteInclusion->delete();

            return response()->json([
                'message' => 'Equipment Deleted Successfully',
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Update a Data', 'error' => $e->getMessage()], 500);
        }
    }
    
    //--------------------- End Add Equip or Inclusion ---------------------------------------

    
    //-------------------- Display Apartment and Boardinghouse belongs to the property-----------------------------
    public function Show_all($id) #for filter category 
    {
        try{

            $show = Property::with(['apartments', 'boardingHouses', ])->findOrfail($id);
            $room = [];
            if ($show->boardingHouses && $show->boardingHouses->isNotEmpty()) {
                $boardinghouse_id = $show->boardingHouses->pluck('id');
                $room = BoardingHouse::with(['rooms'])->whereIn('id', $boardinghouse_id)->get();
            } elseif ($show->apartments && $show->apartments->isNotEmpty()) {
                $apartment_id = $show->apartments->pluck('id');
            } else {
                return response()->json([
                    'message' => 'No Boarding House or Apartment Data',
                ]);
            }
            return response()->json([
                'message' => 'Property_Types Query successfully Created!',
                $show,
                $room
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
    }

    public function ShowAll_Apartment($id) #for filter category 
    {
        try{
            $showApartment = Property::with(['apartments'])->findOrfail($id);

            $apartment = $showApartment->apartments()->with('inclusions.inclusion')->get();

            if(!$apartment){
                return response()->json(['message' => 'Apartment Property Not Found']);
            }

            if($apartment){
                return response()->json([
                    'message' => 'All Apartment Property Found Successfully',
                    'data' => $apartment,
                    // 'inclusion' => $inclusions
                ], 202);
            }
        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }

    }

    public function showAll_BoardingHouse($id) #for filter category 
    {
        try{
            $showBoardinghouse = Property::with(['boardingHouses'])->findOrfail($id);

            $boardinghouse = $showBoardinghouse->boardinghouses()->with( 'rooms', 'inclusions.inclusion')->get();

            if(!$boardinghouse){
                return response()->json(['message' => 'Boarding House Property Not Found']);
            }

            if($boardinghouse){
                return response()->json([
                    'message' => 'All Boarding House Property Found Successfully',
                    'data' => $boardinghouse,
                    // 'inclusion' => $inclusions
                ], 202);
            }
        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
    }

    public function showAllByStatus($id, $status) #for filter category 
    {
        try{
            $showStatus = Property::with(['apartments', 'boardingHouses'])->findOrfail($id);

            $statusApartment = $showStatus->apartments()->with('inclusions.inclusion')->where('status', $status)->get();
            $statusBoardinghouse = $showStatus->boardingHouses()->with('rooms', 'inclusions')->where('status', $status)->get();


            // $status = $showstatus->apartments()->with('inclusions.inclusion')->get();
            // $status2 = $showstatus->boardingHouses()->with('rooms', 'inclusions')->get();

            if(!$statusApartment && !$statusBoardinghouse){
                return response()->json([
                    'message' => 'status Property Not Found',
                ], 404);
            }
            if($statusApartment && $statusBoardinghouse){
                return response()->json([
                    'message' => 'Available Property Found Successfully',
                    'status' => $statusApartment, $statusBoardinghouse
                ], 201);
            }

        }catch(\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
    }








    // Display the Details of boardinghouse and apartment
    public function Show_Details_Apartment($propertyid, $unitId) #display information of apartment when view button is click
    {
        try{
            $property  = Property::with(['apartments'])->where('id', $propertyid)->first();

            if(!$property)
            {
                return response()->json(['message' => 'Property not Found'], 404);
            }

            $apartment = $property->apartments()->with('inclusions.inclusion', 'property')->where('id', $unitId)->first(); // connecting to relation

            if (!$apartment)
            {
                return response()->json(['message' => 'Not Found']);
            }

        
            $inclusions = $apartment->inclusions;

            if($apartment)
            {
                return response()->json([
                    'message' => 'Unit Property Found Successfully',
                    'apartment' => $apartment,
                    // 'inclusion' => $inclusions
                ]);
            }
        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }

    }

    public function Show_Details_Boardinghouse($propertyid, $unitId) #display information of boarding house when view button is click
    {
        try{
            $property  = Property::with(['boardingHouses'])->where('id', $propertyid)->first();

            if(!$property)
            {
                return response()->json(['message' => 'Property not Found'], 404);
            }

            $boardinghouse = $property->boardinghouses()->with('rooms.beds',  'inclusions.inclusion', 'property')->where('id', $unitId)->first();

          
            if (!$boardinghouse)
            {
                return response()->json(['message' => 'Not Found']);
            }

            if($boardinghouse)
            {
                return response()->json([
                    'message' => 'Unit Property Found Successfully',
                    'boardinghouse' => $boardinghouse,
                ]);
            }
        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }

    }

    // ------------------------------------EDIT(UPDATE) and DELETE APARTMENT---------------------------------------------

    // Edit for Apartment and Boarding house
    public function Edit_Apartment($id)
    {
       try{
            $info = Apartment::with('inclusions.inclusion')->where('id', $id)->first();
            return response()->json([
                'Message' => 'Edit Query Successfully Created',
                'apartment' => $info,
            ]);
       }catch (\Exception $e) {
        return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }

    }

    public function Update_Apartment(Request $request, $id)
    {
        try{
            $validateData = $request->validate([
                'propertyid' => 'required|integer',
                'apartmentname' => 'required|string|max:24',
                'numberofrooms' => 'required|integer',
                'capacity' => 'required|integer',
                'rentalfee' => 'required|numeric',
                'payorname' => 'nullable|string',
                'status' => 'required|string',
                'property_type' => 'required|string',
                'inclusion' => 'required|string',
                'buildingno' => 'required|string',
                'street' => 'required|string|max:16',
                'barangay' => 'required|string|max:16',
                'municipality' => 'required|string|max:16',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3050',
                
            ]);

            $apartment = Apartment::find($id);
            if(!$apartment){
                return response()->json([
                    'message' => 'Apartment Not Found',
                ], 404);
            }

            $inclusions = json_decode($validateData['inclusion'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'message' => 'Invalid inclusion format.',
                ], 400);
            }

            $inclusionNames = implode(', ', array_map(function ($inclusion) {
                return isset($inclusion['name']) ? $inclusion['name'] : 'Unknown Inclusion';
            }, $inclusions));

            $fileName = $apartment->image; // Default to existing image
    
            if ($request->hasFile('image')) {
                $fileName = time() . '.' . $request->file('image')->getClientOriginalExtension();
                $imagePath = 'images/' . $fileName; // Store relative path
                $request->file('image')->move(public_path('ApartmentImage'), $fileName); // Move to public/images
            }

            $apartment->property_id = $validateData['propertyid'];
            $apartment->apartment_name = $validateData['apartmentname'];
            $apartment->number_of_rooms = $validateData['numberofrooms'];
            $apartment->capacity = $validateData['capacity'];
            $apartment->rental_fee = $validateData['rentalfee'];
            $apartment->payor_name = $validateData['payorname'];
            $apartment->status = $validateData['status'];
            $apartment->property_type = $validateData['property_type'];
            $apartment->building_no = $validateData['buildingno'];
            $apartment->street = $validateData['street'];
            $apartment->barangay = $validateData['barangay'];
            $apartment->municipality = $validateData['municipality'];
            $apartment->image = $fileName;

            $apartment->save();

           // Get the current inclusions associated with the apartment
            $existingInclusions = ApartmentInclusion::where('apartment_id', $apartment->id)->get();
            
            // Create a list of updated inclusion IDs for comparison
            $updatedInclusionIds = array_map(function ($inclusion) {
                return $inclusion['id'];
            }, $inclusions);

            // Remove old inclusions that are no longer in the updated list
            foreach ($existingInclusions as $existingInclusion) {
                if (!in_array($existingInclusion->inclusion_id, $updatedInclusionIds)) {
                    $existingInclusion->delete();
                }
            }

            // Update existing inclusions or add new ones
            foreach ($inclusions as $inclusion) {
                $apartmentInclusion = ApartmentInclusion::where('apartment_id', $apartment->id)
                    ->where('inclusion_id', $inclusion['id'])
                    ->first();

                if ($apartmentInclusion) {
                    // Update existing inclusion
                    $apartmentInclusion->quantity = $inclusion['quantity'];
                    $apartmentInclusion->save();
                } else {
                    // Add new inclusion
                    ApartmentInclusion::create([
                        'apartment_id' => $apartment->id,
                        'inclusion_id' => $inclusion['id'],
                        'quantity' => $inclusion['quantity'],
                    ]);
                }
            }

            return response()->json([
                'message' => 'Apartment Updated Successfully',
                'Update' => $apartment
            ], 201);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update Apartment!', 'error' => $e->getMessage()], 500);
        }
    }

    public function Delete_Apartment($id)
    {
        try{
            $deleteApartment = Apartment::with('inclusions.inclusion')->where('id', $id)->first();
    
            // If boardinghouse does not exist, return a not found response
            if (!$deleteApartment) {
                return response()->json([
                    'message' => 'Boardinghouse Not Found!'
                ], 404);
            }
    
            // Delete inclusions if they exist
            if ($deleteApartment->inclusions) {
                foreach ($deleteApartment->inclusions as $inclusion) {
                    $inclusion->delete();
                }
            }

            $deleteApartment->delete();

            return response()->json([
                'message' => 'Boardinghouse Deleted Successfully!',
            ], 200);


        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Delete Apartment!', 'error' => $e->getMessage()], 500);
        }
    }





    // ------------------------------------EDIT(UPDATE) DELETE BOARDINGHOUSE---------------------------------------------

    public function Edit_Boardinghouse($id)
    {
       try{
            $info = BoardingHouse::with('rooms', 'inclusions.inclusion')->where('id', $id)->first();
            return response()->json([
                'Message' => 'Edit Query Successfully Created',
                'boardinghouse' => $info,
            ]);
       }catch (\Exception $e) {
        return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }

    }

    public function Update_Boardinghouse(Request $request, $id){
        try{
            $validateData = $request->validate([
                'propertyid' => 'required|integer',
                'boardinghousename' => 'required|string|max:24',
                'numberofrooms' => 'required|integer',
                'capacity' => 'required|integer',
                'rentalfee' => 'required|numeric',
                'payorname' => 'nullable|string',
                'status' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3050',
                'property_type' => 'required|string',
                'inclusion' => 'required|string',
                'buildingno' => 'required|string',
                'street' => 'required|string|max:16',
                'barangay' => 'required|string|max:16',
                'municipality' => 'required|string|max:16',
                'rooms' => 'array|required',
                'rooms.*.room_number' => 'required|integer',
                'rooms.*.number_of_beds' => 'required|integer',
                
            ]);
    
            $boardinghouse = BoardingHouse::find($id);
            if (!$boardinghouse) {
                return response()->json([
                    'message' => 'Boarding House Not Found',
                ], 404);
            }
    
            $inclusions = json_decode($validateData['inclusion'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'message' => 'Invalid inclusion format.',
                ], 400);
            }
    
            $inclusionNames = implode(', ', array_map(function ($inclusion) {
                return isset($inclusion['name']) ? $inclusion['name'] : 'Unknown Inclusion';
            }, $inclusions));

            $fileName = $boardinghouse->image; // Default to existing image

    
            if ($request->hasFile('image')) {
                $fileName = time() . '.' . $request->file('image')->getClientOriginalExtension();
                $imagePath = 'images/' . $fileName; // Store relative path
                $request->file('image')->move(public_path('ApartmentImage'), $fileName); // Move to public/images
            }
    
            $boardinghouse->property_id = $validateData['propertyid'];
            $boardinghouse->boarding_house_name = $validateData['boardinghousename'];
            $boardinghouse->number_of_rooms = $validateData['numberofrooms'];
            $boardinghouse->capacity = $validateData['capacity'];
            $boardinghouse->rental_fee = $validateData['rentalfee'];
            $boardinghouse->payor_name = $validateData['payorname'];
            $boardinghouse->status = $validateData['status'];
            $boardinghouse->image = $fileName;
            $boardinghouse->property_type = $validateData['property_type'];
            $boardinghouse->building_no = $validateData['buildingno'];
            $boardinghouse->street = $validateData['street'];
            $boardinghouse->barangay = $validateData['barangay'];
            $boardinghouse->municipality = $validateData['municipality'];
    

            $boardinghouse->save();
    
            // Get the current inclusions associated with the apartment
            $existingInclusions = BoardingHouseInclusion::where('boardinghouse_id', $boardinghouse->id)->get();
                
            // Create a list of updated inclusion IDs for comparison
            $updatedInclusionIds = array_map(function ($inclusion) {
                return $inclusion['id'];
            }, $inclusions);
    
            // Remove old inclusions that are no longer in the updated list
            foreach ($existingInclusions as $existingInclusion) {
                if (!in_array($existingInclusion->inclusion_id, $updatedInclusionIds)) {
                    $existingInclusion->delete();
                }
            }
    
            // Update existing inclusions or add new ones
            foreach ($inclusions as $inclusion) {
                $boardinghouseInclusion = BoardingHouseInclusion::where('boardinghouse_id', $boardinghouse->id)
                    ->where('inclusion_id', $inclusion['id'])
                    ->first();
    
                if ($boardinghouseInclusion) {
                    // Update existing inclusion
                    $boardinghouseInclusion->quantity = $inclusion['quantity'];
                    $boardinghouseInclusion->save();
                } else {
                    // Add new inclusion
                    BoardingHouseInclusion::create([
                        'boardinghouse_id' => $boardinghouse->id,
                        'inclusion_id' => $inclusion['id'],
                        'quantity' => $inclusion['quantity'],
                    ]);
                }
            }
    
            // Handle Rooms
            $existingRooms = Room::where('boardinghouse_id', $boardinghouse->id)->get();
            $updatedRooms = $validateData['rooms'];
    
            // Create a list of updated room numbers for comparison
            $updatedRoomNumbers = array_map(function ($room) {
                return $room['room_number'];
            }, $updatedRooms);
    
            // Remove old rooms that are not in the updated list
            foreach ($existingRooms as $existingRoom) {
                if (!in_array($existingRoom->room_number, $updatedRoomNumbers)) {
                    $existingRoom->delete();
                }
            }
    
            // Update existing rooms or add new ones
            foreach ($updatedRooms as $room) {
                $existingRoom = Room::where('boardinghouse_id', $boardinghouse->id)
                    ->where('room_number', $room['room_number'])
                    ->first();
    
                if ($existingRoom !== null) {
                    // Update existing room
                    $existingRoom->number_of_beds = $room['number_of_beds'];
                    $existingRoom->save();
                } else {
                    // Add new room
                    Room::create([
                        'boardinghouse_id' => $boardinghouse->id,
                        'room_number' => $room['room_number'],
                        'number_of_beds' => $room['number_of_beds'],
                    ]);
                }
            }
    
            return response()->json([
                'message' => 'Boardinghouse Updated Successfully',
                'Update' => $boardinghouse
            ], 201);

        }catch(\Exception $e){
            return response()->json(['message' => 'Error to Update a Data', 'error' => $e->getMessage()], 500);
        }
    }

    public function Delete_Boardinghouse($id)
    {
        try {
            // Retrieve the boardinghouse along with its related rooms and inclusions
            $deleteBoardinghouse = BoardingHouse::with('rooms', 'inclusions.inclusion')->where('id', $id)->first();
    
            // If boardinghouse does not exist, return a not found response
            if (!$deleteBoardinghouse) {
                return response()->json([
                    'message' => 'Boardinghouse Not Found!'
                ], 404);
            }
    
            // Delete inclusions if they exist
            if ($deleteBoardinghouse->inclusions) {
                foreach ($deleteBoardinghouse->inclusions as $inclusion) {
                    $inclusion->delete();
                }
            }
    
            // Delete rooms if they exist
            if ($deleteBoardinghouse->rooms) {
                foreach ($deleteBoardinghouse->rooms as $room) {
                    $room->delete();
                }
            }
    
            // Delete the boardinghouse itself
            $deleteBoardinghouse->delete();
    
            return response()->json([
                'message' => 'Boardinghouse Deleted Successfully!',
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Delete Boardinghouse!', 'error' => $e->getMessage()], 500);
        }
    }

   




    




    











    

    // public function Create_unit(Request $request)
    // {

    //     try{

    //         $validateData = $request->validate([
    //             'apartment_id' => 'required|integer',
    //             'unitname' => 'required|string|max:16',
    //             'rentalfee' => 'required|numeric',
    //             'type' => 'required|string',
    //             'status' => 'required|string',
    //             'tenantName' => 'nullable|string',
    //             'tenantContact' => 'nullable|string',
    //         ]);
    
    //         $data = [
    //             'apartment_id' => $validateData['apartment_id'],
    //             'unit_name' => $validateData['unitname'],
    //             'rental_fee' => $validateData['rentalfee'],
    //             'type' => $validateData['type'],
    //             'status' => $validateData['status'],
    //             'tenant_name' => $validateData['tenantName'],
    //             'tenant_contact' => $validateData['tenantContact'],
                
    //         ];

    //         $unitData = Units::create($data);

    //         return(
    //             response()->json([
    //                 'message' => 'Created Apartment Successfully',
    //                 'data' => $unitData,
    //             ])
    //         );

    //     }catch (\Exception $e) {
    //         return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
    //     }
    // }
}
