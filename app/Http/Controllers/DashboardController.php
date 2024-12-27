<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BoardingHouse;
use App\Models\Property;
use App\Models\Bed;
use App\Models\Apartment;
use App\Models\Account;
use App\Models\Expenses;
use App\Models\Revenue;
use App\Models\MaintenanceRequest;
use App\Models\ScheduleMaintenance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function Index()
    {
        try{
            $allProperty =  ([
                'boardingHouse' => BoardingHouse::count(), 
                'apartment' => Apartment::count(),
                'type1' => 'Boarding House',
                'type2' => 'Apartment',
            ]);

            $allTenant = ([
                'tenant' =>  $allTenant = Account::where('user_type', 'User')
                ->where('status', 'Active')
                ->count(),
                'user' => 'Tenant',
            ]);

            $allBed = ([
                'Availablebed' =>  $allBed = Bed::where('status', 'available')->count(),
                'occupiedbed' =>  $allBed = Bed::where('status', 'occupied')->count(),
                'status1' => 'Available Beds',
                'status2' => 'Total Occupied Beds'
            ]);

            $requestMaintenance = ([
                'totalReqeust' => $count = MaintenanceRequest::where('status', 'pending')->count(),
                'upcomingMaintenance' => $count = ScheduleMaintenance::where('status', 'To do')->count(),
                'finishedMaintenance' => $count = ScheduleMaintenance::where('status', 'Completed')->count(),
                'inProgress' => $count = ScheduleMaintenance::where('status', 'In Progress')->count(),
            ]);

            return response()->json([
                'Message' => 'Qeury Data Successfully Created!',
                'data' => $allProperty,  $allTenant, $allBed, $requestMaintenance
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function All_Tenant()
    {
        try{
            $allTenantList = Account::where('user_type', 'User')->select('id', 'firstname', 'lastname', 'contact')->get();
            
            return response()->json([
                'Message' => 'Qeury Data Successfully Created!',
                'data' => $allTenantList,
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function GetIncome(Request $request)
    {
        try {
     
            $type = $request->input('type', 'monthly'); 

            // Get the current year and month using Carbon
            $currentYear = Carbon::now()->year;
            $currentMonth = Carbon::now()->month;

            // Determine the year and month based on the request type
            $year = $request->input('year', $currentYear); 
            $month = $type === 'monthly' ? $request->input('month', $currentMonth) : null;

            // Query all revenues for the current year
            $queryRevenues = Revenue::where('year', $year);
            
            // Query all expenses for the current year
            $queryExpenses = Expenses::whereYear('expense_date', $year);

            // If the type is 'monthly', add the month filter
            if ($type === 'monthly') {
                $queryRevenues = $queryRevenues->where('month', $month);
                $queryExpenses = $queryExpenses->whereMonth('expense_date', $month);
            }

            // Fetch the data
            $revenues = $queryRevenues->get();
            $expenses = $queryExpenses->get();

            // Calculate totals
            $totalIncome = $revenues->sum('total_amount');
            $totalExpenses = $expenses->sum('amount');
            $totalRevenue = $totalIncome - $totalExpenses;

            // Previous income and expenses logic
            $prevTotalIncome = 0;
            $prevTotalExpenses = 0;

            if ($type === 'monthly') {
                $previousDate = Carbon::create($year, $month, 1)->subMonth();
                $prevTotalIncome = Revenue::where('year', $previousDate->year)
                    ->where('month', $previousDate->month)
                    ->sum('total_amount');
                $prevTotalExpenses = Expenses::whereYear('expense_date', $previousDate->year)
                    ->whereMonth('expense_date', $previousDate->month)
                    ->sum('amount');
            } else {
                $prevTotalIncome = Revenue::where('year', $year - 1)->sum('total_amount');
                $prevTotalExpenses = Expenses::whereYear('expense_date', $year - 1)->sum('amount');
            }

            $prevNetIncome = $prevTotalIncome - $prevTotalExpenses;
            $percentageChange = 'No Change';
            if ($prevNetIncome > 0) {
                $totalIncomePercentage = round((($totalRevenue - $prevNetIncome) / $prevNetIncome) * 100, 2);
                $percentageChange = $totalIncomePercentage > 0 ? 'Increase' : 'Decrease';
            } else {
                $totalIncomePercentage = 0;
            }

            $data = [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'total_revenue' => $totalRevenue,
                'percentage_change' => $percentageChange,
                'income_percentage' => $totalIncomePercentage,
            ];

            // Return the response as JSON
            return response()->json([
                'message' => 'Successfully retrieve the Income Data!',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching data', 'error' => $e->getMessage()], 500);
        }
    }

    public function GetExpenses(Request $request)
    {
        try{
            $type = $request->input('type', 'monthly'); 
            // Get the current year and month using Carbon
            $currentYear = Carbon::now()->year;
            $currentMonth = Carbon::now()->month;

            $year = $request->input('year', $currentYear); 
            $month = $type === 'monthly' ? $request->input('month', $currentMonth) : null;

            $queryExpenses = Expenses::whereYear('expense_date', $year);

            // If the type is 'monthly', add the month filter
            if ($type === 'monthly') {
                $queryExpenses = $queryExpenses->whereMonth('expense_date', $month);
            }

            $expenses = $queryExpenses->get();

            $totalExpenses = $expenses->sum('amount');// current total

            $prevTotalExpenses = 0;
            if ($type === 'monthly') {
                $previousDate = Carbon::create($year, $month, 1)->subMonth();
                $prevTotalExpenses = Expenses::whereYear('expense_date', $previousDate->year)
                    ->whereMonth('expense_date', $previousDate->month)
                    ->sum('amount');
            } else {
                $prevTotalExpenses = Expenses::whereYear('expense_date', $year - 1)->sum('amount');
            }

            $percentageChange = 0;
            $precentageType = 'No Change';
            if($prevTotalExpenses > 0){
                $percentageChange = round((($totalExpenses - $prevTotalExpenses) / $prevTotalExpenses) * 100, 2);
                if($percentageChange > 0){
                    $precentageType = 'Increase';
                }else{
                    $precentageType = 'Decrease';
                }
            }

            $data = [
                'currentExpenses' => $totalExpenses,
                'previousExpenses' => $prevTotalExpenses,
                'percentageChange' => $percentageChange,
                'precentageType' => $precentageType,
            ];

            return response()->json([
                'message' => 'Successfully Retrieve the Expenses',
                'data' =>  $data
            ]);


        }catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching data', 'error' => $e->getMessage()], 500);
        }
    }

    public function all()
    {
        try{
            $properties = Property::with(['apartments', 'boardingHouses'])->get();
            $result = $properties->map(function ($property) {
                $available = ($property->boardingHouses->where('status', 'Available')->count() +  $property->apartments->where('status', 'Available')->count()); 
                $occupied = ($property->boardingHouses->where('status', 'Occupied')->count() +  $property->apartments->where('status', 'Occupied')->count()); 
                
                return [
                    'id' => $property->id,
                    'propertyname' => $property->propertyname,
                    'barangay' => $property->barangay,
                    'municipality' => $property->municipality,
                    'occupied' => $occupied,
                    'available' => $available,
                ];
            });

            return response()->json([
                'message' => 'Data Retrieved Successfully',
                'data' => $result
            ]);
    
        }catch (\Exception $e) {
            return response()->json(['message' => 'Error to Query a Data', 'error' => $e->getMessage()], 500);
        }
    }



}
