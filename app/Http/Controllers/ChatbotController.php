<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Auth;
use App\Models\KnowledgeBase;
use App\Models\BoardingHouse;
use App\Models\Apartment;
use App\Models\Deliquent;
use App\Models\PaymentTransactions;
use App\Models\RentalAgreement;
use App\Models\Account;
use App\Models\MaintenanceRequest;
use App\Models\ScheduleMaintenance;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    protected $fallbackResponse = 'Pasensya na, hindi ko alam ang sagot sa tanong na iyan. May mga limitasyon ang aking kaalaman.';

    
    public function allApartment()
    {
        try{
            $allApartment = Apartment::all();
            

            return response()->json([
                'apartment' => $allApartment,
            ]);
        }catch (\Exception $e) {
            return $this->$fallbackResponse;
        }
    }

    public function allBoardingHouse()
    {
        try{
            $boardingHouse = BoardingHouse::all();

            if(!$boardingHouse){
                return response()->json(['message' => 'Pasensya na, wala pang boarding house na naka register']);
            }

            return response()->json([
                'data' => $boardingHouse
            ]);
        }catch (\Exception $e) {
            return $this->$fallbackResponse;
        }
    }

    public function Available_BoardingHouse_Response()
    {
        try {
            $available = BoardingHouse::with(['inclusions.equipment', 'rooms.beds' => function($query) {
                $query->where('status', 'Available');
            }])
            ->where('status', 'Available')
            ->get();

            // Filter out boarding houses with no available beds
            $available = $available->filter(function($boardingHouse) {
                return $boardingHouse->rooms->contains(function($room) {
                    return $room->beds->isNotEmpty();
                });
            });

            if ($available->isEmpty()) {
                return response()->json('Sa ngayon walang available na unit para sa boarding house', 404);
            }

            return response()->json([
                'data' => $available
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query Data', 'error' => $e->getMessage()], 500);
        }
    }

    public function Available_Apartment_Response()
    {
        try{
            $available = Apartment::with('inclusions.equipment')->where('status', 'Available')->get();
            if(!$available){
                return response()->json('Sa ngayon walang available na unit para sa apartment');
            }
            return response()->json([
             'data' => $available   
            ]);
        } catch (\Exception $e) {
            return $this->fallbackResponse;
        }
    }

    #this function is for rental fee is less than 5000
    public function apartmentsPriceRangeBetween5000()
    {
        try{
            $apartmentPrice = Apartment::with('inclusions.equipment')
            ->where('status', 'Available')
            ->where('rental_fee', '<', 5000)
            ->get();

            if(!$apartmentPrice){
                return response()->json('Sa ngayon walang available na unit para sa apartment');
            }
            return response()->json([
                'data' => $apartmentPrice
            ]);

        } catch (\Exception $e) {
            // return $this->fallbackResponse;
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
    }

    #this function is for rental fee is greater than 5000 to 15000
    public function apartmentsPriceRangeBetween5000To15000()
    {
        try{
            $apartmentPrice = Apartment::with('inclusions.equipment')
            ->where('status', 'Available')
            ->whereBetween('rental_fee', [5001, 14999]) // Between 5000 and 15000
            ->get();

            if ($apartmentPrice->isEmpty()) {
                return response()->json(['message' => 'Sa ngayon, walang available na unit para sa presyo ng apartment.']);
            }
            return response()->json([
                'data' => $apartmentPrice
            ]);
        } catch (\Exception $e) {
            return $this->fallbackResponse;
        }
    }

    #this function is for rental fee is greater than 15000
    public function apartmentsPriceRangeGreater15000()
    {
        try{
            $apartmentPrice = Apartment::with('inclusions.equipment')
            ->where('status', 'Available')
            ->where('rental_fee', '>', 15000)
            ->get();
            if ($apartmentPrice->isEmpty()) {
                return response()->json(['message' => 'Sa ngayon, walang available na unit para sa presyo ng apartment.']);
            }

            return response()->json([
                'data' => $apartmentPrice
            ]);
        } catch (\Exception $e) {
            return $this->fallbackResponse;
        }
    }

    public function boardinghousePriceRangeLessThan2000()
    {
        try{
            $boardinghousePrice = BoardingHouse::with(['inclusions.equipment', 'rooms.beds' => function($query) {
                $query->where('status', 'Available')
                ->where('price', '<=', 2000);
            }])
            ->where('status', 'Available')
            ->get();

            $result = $boardinghousePrice->filter(function($boardingHouse) {
                return $boardingHouse->rooms->contains(function($room) {
                    return $room->beds->isNotEmpty();
                });
            });

            if($result->isEmpty()){
                return response()->json(['message' => 'Sa ngayon, walang available na unit para presyo na nais mo']);
            }

            return response()->json([
                'data' => $result
            ]);

        }catch (\Exception $e) {
            return $this->fallbackResponse;
        }
    }

    public function boardinghousePriceRangeBetween2000To5000()
    {
        try{
            $boardinghousePrice = BoardingHouse::with(['inclusions.equipment', 'rooms.beds' => function($query) {
                $query->where('status', 'Available')
                ->whereBetween('price', [2001, 50000])
                ->get();
            }])
            ->where('status', 'Available')
            ->get();

            $result = $boardinghousePrice->filter(function($boardingHouse) {
                return $boardingHouse->rooms->contains(function($room) {
                    return $room->beds->isNotEmpty();
                });
            });

            if($result->isEmpty()){
                return response()->json(['message' => 'Sa ngayon, walang available na unit para presyo na nais mo']);
            }

            return response()->json([
                'data' => $result
            ]);

        }catch (\Exception $e) {
            return $this->fallbackResponse;
        }
    }

    public function boardinghousePriceGreaterThan5000()
    {
        try{
            $boardinghousePrice = BoardingHouse::with(['inclusions.equipment', 'rooms.beds' => function($query) {
                $query->where('status', 'Available')
                ->where('price', '>', 5000)
                ->get();
            }])
            ->where('status', 'Available')
            ->get();

            $result = $boardinghousePrice->filter(function($boardingHouse) {
                return $boardingHouse->rooms->contains(function($room) {
                    return $room->beds->isNotEmpty();
                });
            });

            if($result->isEmpty()){
                return response()->json(['message' => 'Sa ngayon, walang available na unit para presyo na nais mo']);
            }

            return response()->json([
                'data' => $result
            ]);

        }catch (\Exception $e) {
            return $this->fallbackResponse;
        }
    }

    public function LastPayment($firstname, $lastname, $unitName, $unitType)
    {
        try{
            $user = Account::where('firstname', $firstname)
            ->where('lastname', $lastname)
            ->first();

            if (!$user) {
                return response()->json(['message' => 'Pasensya na, hindi ko mahanap ang iyong pangalan. Siguraduhing tama ang iyong ibinigay at nakarehistro sa aming sistema.']);
            }

            $userId = $user->id;

            $unitRented = RentalAgreement::with('rentedUnit')
            ->where('tenant_id', $userId)
            ->first();

            if (!$unitRented || !$unitRented->rentedUnit) {
                return response()->json([
                    'message' => 'Pasensya na, wala kang kasalukuyang rental agreement.'
                ], 200);
            }

            $isBoardingHouse = strtolower($unitRented->rentedUnit->property_type) === 'boarding house';
            $isApartment = strtolower($unitRented->rentedUnit->property_type) === 'apartment';
    
            // Validate unit name and type
            if ($isBoardingHouse) {
                if ($unitRented->rentedUnit->boarding_house_name !== $unitName || $unitRented->rentedUnit->property_type !== $unitType) {
                    return response()->json([
                        'message' => 'Pasensya na, hindi ko mahanap ang boarding house na iyong tinutukoy. Siguraduhing tama ang iyong ibinigay na unit name at unit type.'
                    ], 200);
                }
            } elseif ($isApartment) {
                if ($unitRented->rentedUnit->apartment_name !== $unitName || $unitRented->rentedUnit->property_type !== $unitType) {
                    return response()->json([
                        'message' => 'Pasensya na, hindi ko mahanap ang apartment na iyong tinutukoy. Siguraduhing tama ang iyong ibinigay na unit name at unit type.'
                    ], 200);
                }
            } else {
                return response()->json([
                    'message' => 'Pasensya na, hindi ko mahanap ang unit na iyong tinutukoy. Siguraduhing tama ang iyong ibinigay na unit name at unit type.'
                ], 200);
            }
    
            // Validate unit name and type
            // if ($unitRented->rentedUnit->boarding_house_name !== $unitName || 
            //     $unitRented->rentedUnit->property_type !== $unitType) {
            //     return response()->json([
            //         'message' => 'Pasensya na, hindi ko mahanap ang unit na iyong tinutukoy. Siguraduhing tama ang iyong ibinigay na unit name at unit type'
            //     ], 200);
            // }

            $lastpayment = PaymentTransactions::where('tenant_id', $userId)
            ->whereIn('transaction_type', ['Advance Payment', 'Rental Fee', 'Initial Payment'])
            ->orderBy('date', 'desc')
            ->first();

            return response()->json([
                'data' => $lastpayment,
                // 'unit' => $unitRented
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Pasensya na, sa ngayon hindi ko ma iproseso ang nais mo.']);
        }
    }

    public function findScheduleMaintenance($firstname, $lastname, $reportedItem, $reportedDate)
    {
        try{
            $user = Account::where('firstname', $firstname)
            ->where('lastname', $lastname)
            ->first();

            if (!$user) {
                return response()->json(['message' => 'Pasensya na, hindi ko mahanap ang iyong pangalan. Siguraduhing tama ang iyong ibinigay at nakarehistro sa aming sistema.']);
            }

            $userId = $user->id;

            \Log::info('Reported Item:', ['reportedItem' => $reportedItem]);
            \Log::info('Reported Date:', ['reportedDate' => $reportedDate]);
           // Retrieve maintenance requests that match the reportedItem and reportedDate
            $maintenanceRequests = MaintenanceRequest::where('tenant_id', $userId)
            ->where(function($query) use ($reportedItem) {
                $query->where('reported_issue', 'like', '%' . $reportedItem . '%')
                    ->orWhere('other_issue', 'like', '%' . $reportedItem . '%');
            })
            ->where('date_reported', $reportedDate)
            ->where('is_schedule', 1)
            ->get();

            if ($maintenanceRequests->isEmpty()) {
                return response()->json([
                    'message' => 'Walang maintenance request na tumutugma sa iyong ibinigay na detalye.'
                ], 200);
            }

            $id = $maintenanceRequests->first()->id;

            // Query the ScheduleMaintenance with the extracted id
            $schedule = ScheduleMaintenance::with('maintenanceRequest')->where('maintenance_request_id', $id)->first();
            
            if(!$schedule){
                return response()->json([
                    'message' => 'Pasensya na, sa ngayon ang iyong request ay hindi pa naka schedule para sa pag-aayos nito.'
                ]);
            }
           
            return response()->json([
                'data' => $schedule
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
            return response()->json(['message' => 'Pasensya na, sa ngayon hindi ko ma iproseso ang nais mo.']);
        }
    }

    public function findMaintenanceRequestStatus($firstname, $lastname, $reportedItem, $reportedDate, $unitName, $unitType)
    {
        try{
            $user = Account::where('firstname', $firstname)
            ->where('lastname', $lastname)
            ->first();

            if (!$user) {
                return response()->json(['message' => 'Pasensya na, hindi ko mahanap ang iyong pangalan. Siguraduhing tama ang iyong ibinigay at nakarehistro sa aming sistema.']);
            }

            $userId = $user->id;

            $unitRented = RentalAgreement::with('rentedUnit')
            ->where('tenant_id', $userId)
            ->first();

            if (!$unitRented || !$unitRented->rentedUnit) {
                return response()->json([
                    'message' => 'Pasensya na, wala kang kasalukuyang rental agreement.'
                ], 200);
            }

            $isBoardingHouse = strtolower($unitRented->rentedUnit->property_type) === 'boarding house';
            $isApartment = strtolower($unitRented->rentedUnit->property_type) === 'apartment';
    
            // Validate unit name and type
            if ($isBoardingHouse) {
                if ($unitRented->rentedUnit->boarding_house_name !== $unitName || $unitRented->rentedUnit->property_type !== $unitType) {
                    return response()->json([
                        'message' => 'Pasensya na, hindi ko mahanap ang boarding house na iyong tinutukoy. Siguraduhing tama ang iyong ibinigay na unit name at unit type.'
                    ], 200);
                }
            } elseif ($isApartment) {
                if ($unitRented->rentedUnit->apartment_name !== $unitName || $unitRented->rentedUnit->property_type !== $unitType) {
                    return response()->json([
                        'message' => 'Pasensya na, hindi ko mahanap ang apartment na iyong tinutukoy. Siguraduhing tama ang iyong ibinigay na unit name at unit type.'
                    ], 200);
                }
            } else {
                return response()->json([
                    'message' => 'Pasensya na, hindi ko mahanap ang unit na iyong tinutukoy. Siguraduhing tama ang iyong ibinigay na unit name at unit type.'
                ], 200);
            }

            $maintenanceRequestsStatus = MaintenanceRequest::where('tenant_id', $userId)
            ->where(function($query) use ($reportedItem) {
                $query->where('reported_issue', 'like', '%' . $reportedItem . '%')
                    ->orWhere('other_issue', 'like', '%' . $reportedItem . '%');
            })
            ->where('date_reported', $reportedDate)
            ->get();

            if($maintenanceRequestsStatus->isEmpty()){
                return response()->json([
                   'message' => 'Walang maintenance request na tumutugma sa iyong ibinigay na detalye. Posibleng mali ang iyong inilagay na impormasyon o wala pang naipadalang maintenance request.'
                ], 200);
            }

            return response()->json([
                'data' => $maintenanceRequestsStatus
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
            return response()->json(['message' => 'Pasensya na, sa ngayon hindi ko ma iproseso ang nais mo.']);
        }
    }

    public function checkBalance($firstname, $lastname, $unitName, $unitType)
    {
        try{
            $user = Account::where('firstname', $firstname)
            ->where('lastname', $lastname)
            ->first();

            if (!$user) {
                return response()->json(['message' => 'Pasensya na, hindi ko mahanap ang iyong pangalan. Siguraduhing tama ang iyong ibinigay at nakarehistro sa aming sistema.']);
            }

            $userId = $user->id;

            $unitRented = RentalAgreement::with('rentedUnit')
            ->where('tenant_id', $userId)
            ->first();

            if (!$unitRented || !$unitRented->rentedUnit) {
                return response()->json([
                    'message' => 'Pasensya na, wala kang kasalukuyang rental agreement.'
                ], 200);
            }

            $isBoardingHouse = strtolower($unitRented->rentedUnit->property_type) === 'boarding house';
            $isApartment = strtolower($unitRented->rentedUnit->property_type) === 'apartment';
    
            // Validate unit name and type
            if ($isBoardingHouse) {
                if ($unitRented->rentedUnit->boarding_house_name !== $unitName || $unitRented->rentedUnit->property_type !== $unitType) {
                    return response()->json([
                        'message' => 'Pasensya na, hindi ko mahanap ang boarding house na iyong tinutukoy. Siguraduhing tama ang iyong ibinigay na unit name at unit type.'
                    ], 200);
                }
            } elseif ($isApartment) {
                if ($unitRented->rentedUnit->apartment_name !== $unitName || $unitRented->rentedUnit->property_type !== $unitType) {
                    return response()->json([
                        'message' => 'Pasensya na, hindi ko mahanap ang apartment na iyong tinutukoy. Siguraduhing tama ang iyong ibinigay na unit name at unit type.'
                    ], 200);
                }
            } else {
                return response()->json([
                    'message' => 'Pasensya na, hindi ko mahanap ang unit na iyong tinutukoy. Siguraduhing tama ang iyong ibinigay na unit name at unit type.'
                ], 200);
            } 

            $totalOverdue = Deliquent::where('tenant_id', $userId)
            ->where('status', 'Overdue')
            ->sum('amount_overdue'); 

            
            if(!$totalOverdue){
                return response()->json([
                    'message' => 'Sa ngayon walang kang balanse'
                ]);
            }

            $dueDates = Deliquent::where('tenant_id', $userId)
            ->where('status', 'Overdue')
            ->pluck('month_overdue'); 
    

            $formattedDueDates = [];
            foreach ($dueDates as $dueDate) {
                $formattedDueDates[] = Carbon::parse($dueDate)->format('F j, Y'); 
            }   
            $dueDatesString = implode(', ', $formattedDueDates);
    
            return response()->json([
                'data' => $totalOverdue,
                'date' => $dueDatesString
            ]);
                

        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
            return response()->json(['message' => 'Pasensya na, sa ngayon hindi ko ma iproseso ang nais mo.']);
        }
    }

    public function landLordContactInfo()
    {
        try{
            $info = Account::where('user_type', 'Landlord')->first();
            if(!$info){
                return response()->json([
                    'message' => 'Pasensya na, wala akong makita na contact information'
                ]);
            }
            // $user = Auth()->user();

            return response()->json([
                'contact' => $info->contact,
                'email' => $info->email,
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
            return response()->json(['message' => 'Pasensya na, sa ngayon hindi ko ma iproseso ang nais mo.']);
        }
    }

    

    
}