<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\validatedData;
use App\Models\Account;
use App\Models\Property;
use App\Models\Expenses;
use App\Models\MaintenanceImages;
use Carbon\Carbon;



class ExpensesController extends Controller
{
    # retrive the property unit for expenses
    public function Get_All_Property()
    {
        try{
            $properties = Property::with('apartments', 'boardingHouses')->get();
            if(!$properties){
                return response()->json(['message', 'No propety Found!']);
            }

            return response()->json([
                'messgae' => 'Query Date successfully',
                'status' => 200,
                'data' => $properties,
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Store_Expenses(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'unitId' => 'required|integer|min:1',
                'type' => 'required|string',
                'category' => 'required|string',
                'type_of_bills' => 'nullable|string',
                'amount' => 'required|numeric',
                'expenseDate' => 'required|date_format:m/d/Y',
                'description' => 'nullable|string|min:1|max:2500',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:3050',
            ]);

            $existing = Expenses::where('unit_id', $validatedData['unitId'])
            ->whereDate('expense_date', Carbon::createFromFormat('m/d/Y', $validatedData['expenseDate'])->format('Y-m-d')) // Check only the date
            ->where('category', $validatedData['category']);

            if (!empty($validatedData['type_of_bills'])) {
                $existing->where('type_of_bills', $validatedData['type_of_bills']);
            }

            $existing = $existing->first();

            if ($existing) {
                return response()->json([
                    'message' => 'Duplicate entry: This category and expense date already exists.'
                ], 409); // 409 Conflict
            }


            $data = [
                'unit_id' => $validatedData['unitId'],
                'unit_type' => $validatedData['type'],
                'amount' => $validatedData['amount'],
                'category' => $validatedData['category'],
                'type_of_bills' => !empty($validatedData['type_of_bills']) ? $validatedData['type_of_bills'] : null,
                'description' => $validatedData['description'],
                'expense_date' => Carbon::createFromFormat('m/d/Y', $validatedData['expenseDate'])->format('Y-m-d'),
                'recurring' => 0,
            ];

            $expenses = Expenses::create($data);
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $fileName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
                    $imageFile->move(public_path('MaintenanceImages'), $fileName);

                    // Create image record in property_images table
                    MaintenanceImages::create([
                        'image_path' => $fileName,
                        'expenses_id' => $expenses->id,
                    ]);
                }
            } 

            return response()->json([
                'message' => 'Expenses Successfully Created!',
                'data' => $data
            ], 201);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Submit Expenses', 'error' => $e->getMessage()], 500);
        }
    }


    public function Edit($id)
    {
        try{
            $expenses = Expenses::with('expensesImages')->find($id);
            if (!$expenses) {
                return response()->json(['message' => 'No data found'], 404);
            }

            return response()->json([
                'message' => 'Successfully Expenses Found!',
                'data' => $expenses
            ]); 
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Update_Expenses(Request $request, $id)
    {
        try{
            $validatedData = $request->validate([
                'unitId' => 'required|integer|min:1',
                'type' => 'required|string',
                'category' => 'required|string',
                'type_of_bills' => 'nullable|string',
                'amount' => 'required|numeric',
                'expenseDate' => 'required|date_format:m/d/Y',
                'description' => 'nullable|string|min:1|max:2500',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:3050',
            ]);

            $updateExpenses = Expenses::find($id);

            if(!$updateExpenses){
                return response()->json(['message' => 'No expenses found to update']);
            }

            $updateExpenses->unit_id = $validatedData['unitId'];
            $updateExpenses->unit_type = $validatedData['type'];
            $updateExpenses->category = $validatedData['category'];
            $updateExpenses->type_of_bills = $validatedData['type_of_bills'];
            $updateExpenses->amount = $validatedData['amount'];
            $updateExpenses->expense_date = Carbon::createFromFormat('m/d/Y', $validatedData['expenseDate'])->format('Y-m-d');
            $updateExpenses->description = $validatedData['description'];

            $updateExpenses->save();

            // Handle image uploads if any
            if ($request->hasFile('images')) {
                // Optionally, delete old images associated with this expense if needed
                MaintenanceImages::where('expenses_id', $updateExpenses->id)->delete();

                foreach ($request->file('images') as $imageFile) {
                    $fileName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
                    $imageFile->move(public_path('MaintenanceImages'), $fileName);

                    // Save the new images
                    MaintenanceImages::create([
                        'image_path' => $fileName,
                        'expenses_id' => $updateExpenses->id,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Expenses Updated Successfully!',
                'data' => $updateExpenses
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Update Expenses', 'error' => $e->getMessage()], 500);
        }
    }

    public function Get_All_Expenses(Request $request)
    {
        try{
            $year = $request->input('year', Carbon::now()->year);
            $month = $request->input('month'); 

            $queryExpenses = Expenses::with('unit')->whereYear('expense_date', $year);

            if(!$queryExpenses){
                return response()->json(['message' => 'No data found'], 404);
            }

            if($month && $month !== 'all'){
                $queryExpenses->whereMonth('expense_date', $month);
            }

            $allExpenses = $queryExpenses->get();

            return response()->json([
                'message' => 'Successfully Expenses Found!',
                'data' => $allExpenses
            ], 200);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Filter_Expenses(Request $request, $category)
    {
        try{
            $validCategory = ['maintenance fee', 'utility bill', 'recurring'];

            $year = $request->input('year', Carbon::now()->year);
            $month = $request->input('month'); // If no month is provided, it's null

            if (!in_array($category, $validCategory)) {
                return response()->json([
                    'message' => 'Invalid Category',
                ], 400);
            }

            $expenseValue = [];

            if($category === 'maintenance fee' || $category === 'utility bill'){
                $query = Expenses::with('unit')->where('category' ,$category)
                ->whereYear('expense_date' ,$year);

                if($month && $month !== 'all'){
                    $query->whereMonth('expense_date' ,$month);
                }

                $filter = $query->get();
                $expenseValue = $filter;
            }
            elseif($category === 'recurring'){
                $query = Expenses::with('unit')->where('recurring', true)
                ->whereYear('expense_date' ,$year);

                if($month && $month !== 'all'){
                    $query->whereMonth('expense_date' ,$month);
                }

                $filter = $query->get();
                $expenseValue = $filter;
            }

            return response()->json([
                'message' => 'Successfully Expenses Found!',
                'data' => $expenseValue
            ], 200);
            
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Delete_Expenses($id)
    {
        try{
            $deleteExpenses = Expenses::find($id);

            if(!$deleteExpenses){
                return response()->json(['message' => 'Expenses not Found!'], 404);
            }

            $deleteExpenses->delete();

            return response()->json([
                'message' => 'Expenses Deleted Successfully!',
                'data' => $deleteExpenses
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Calculate_Expenses(Request $request)
    {
        try {
            $year = $request->input('year', Carbon::now()->year);
            $month = $request->input('month');


            // Query for current period expenses
            $expensesQuery = Expenses::query();
            $expensesQuery->whereYear('expense_date', $year);

            if ($month !== 'all') {
                $expensesQuery->whereMonth('expense_date', $month);
            }

            $expenses = $expensesQuery->get();

            // Calculate total expenses for the current period
            $totalExpenses = $expenses->sum('amount');
            $maxExpenses = $expenses->max('amount');
            $minExpenseRecord = $expenses->sortBy('amount')->first();
            $minExpenseAmount = $minExpenseRecord ? $minExpenseRecord->amount : 0;

    
            $previousMonth = null;

            if ($month !== 'all') {
                $previousMonth = Carbon::createFromDate($year, $month, 1)->subMonth();
                // $previousMonth = Carbon::parse('2025-3-5')->subMonth();
            } else {
                // Handle the case when the month is 'all' (e.g., set to the previous year)
                $previousMonth = Carbon::createFromDate($year - 1, 12, 1); // Set to December of the previous year
            }
            $previousMonthExpenses = Expenses::whereYear('expense_date', $previousMonth->year)
                ->whereMonth('expense_date', $previousMonth->month)
                ->get();

            $previousMonthTotal = $previousMonthExpenses->sum('amount');
            $previousMonthMax = $previousMonthExpenses->max('amount');
            $previousMonthMin = $previousMonthExpenses->min('amount');

            // $limitHighestPercentage = 0;
            // $limitPercentage = 0;
            $highestExpenseChangeType = 'No Change'; // Track whether the percentage change is an increase or decrease
            $lowestExpenseChangeType = 'No Change';

            if($previousMonthMax > 0){
                $highestExpensePercentageChange = round((($maxExpenses - $previousMonthMax) / $previousMonthMax )* 100, 2);
                // $limitHighestPercentage =  min($highestExpensePercentageChange, 100);
                // Determine if it's an increase or decrease
                if ($maxExpenses > $previousMonthMax) {
                    $highestExpenseChangeType = 'Increase';
                } elseif ($maxExpenses < $previousMonthMax) {
                    $highestExpenseChangeType = 'Decrease';
                }
            }else{
                $highestExpensePercentageChange = 0;
            }

            if($previousMonthMin > 0){
                $lowestExpensePercentageChange = round((($minExpenseAmount - $previousMonthMin) / $previousMonthMin) * 100, 2);
                // $limitPercentage = min($lowestExpensePercentageChange, 100);
                    // Determine if it's an increase or decrease
                if ($minExpenseAmount > $previousMonthMin) {
                    $lowestExpenseChangeType = 'Increase';
                } elseif ($minExpenseAmount < $previousMonthMin) {
                    $lowestExpenseChangeType = 'Decrease';
                }
            }else{
                $lowestExpensePercentageChange = 0;
            }

            // Yearly total expenses
            $yearlyExpenses = Expenses::whereYear('expense_date', $year)->sum('amount');
            $previousYearExpenses = Expenses::whereYear('expense_date', $year - 1)->sum('amount');
            $totalExpensePercentageYearly = 0;
            $yearChangeType = 'No Change'; 

            if ($previousYearExpenses > 0) {
                // Calculate percentage change
                $totalExpensePercentageYearly = round((($yearlyExpenses - $previousYearExpenses) / $previousYearExpenses) * 100, 2);
                
                // Determine the change type based on the percentage change
                if ($yearlyExpenses > $previousYearExpenses) {
                    $yearChangeType = 'Increase';
                } elseif ($yearlyExpenses < $previousYearExpenses) {
                    $yearChangeType = 'Decrease';
                }
            }

            // Monthly percentage change (current vs. previous month)
            $monthlyPercentageChange = 0;
            $monthlyChangeType = 'No Change';
            // $limitMonthlyPercentage = 0;
            if($previousMonthTotal > 0){
                $monthlyPercentageChange = round((($totalExpenses - $previousMonthTotal) /$previousMonthTotal) * 100, 2);
                // $limitMonthlyPercentage = min($monthlyPercentageChange, 100);
                if($totalExpenses > $previousMonthTotal){
                    $monthlyChangeType = 'Increase';
                }elseif ($totalExpenses < $previousMonthTotal){
                    $monthlyChangeType = 'Decrease';
                }
            }
            // $monthlyPercentageChange = $previousMonthTotal > 0
            //     ? round((($totalExpenses - $previousMonthTotal) / $previousMonthTotal) * 100, 2)
            //     : 0;

            return response()->json([
                'message' => 'Expenses Calculated Successfully!',
                'year' => $year,
                'month' => $month === 'all' ? 'All Months' : Carbon::create()->month($month)->format('F'),
                'data' => [
                    'prevMonths' => $previousMonth,
                    'total_expenses' => $totalExpenses,
                    'max_expenses' => $maxExpenses,
                    'min_expense_amount' => $minExpenseAmount,
                    'previous_month_total' => $previousMonthTotal,
                    'highest_expense_percentage' => $highestExpensePercentageChange,
                    'highest_expense_change_type' => $highestExpenseChangeType, 
                    'lowest_expense_percentage' => $lowestExpensePercentageChange,
                    'lowest_expense_change_type' => $lowestExpenseChangeType, 
                    'monthly_percentage_change' => $monthlyPercentageChange,
                    'monthly_change_type' => $monthlyChangeType,
                    'yearly_total_expenses' => $yearlyExpenses,
                    'total_expense_percentage_yearly' => $totalExpensePercentageYearly,
                    'yearly_change_type' => $yearChangeType
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }



    public function Expenses_Statistic(Request $request)
    {
        try{
            $year = $request->input('year', Carbon::now()->year);

            // Group expenses by month and calculate totals
            $monthlyExpenses = Expenses::whereYear('expense_date', $year)
                ->selectRaw('MONTH(expense_date) as month, SUM(amount) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->get();
    
            // Format response for chart consumption
            $formattedExpenses = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthData = $monthlyExpenses->firstWhere('month', $i);
                $formattedExpenses[] = [
                    'month' => $i,
                    'total' => $monthData ? $monthData->total : 0,
                ];
            }
    
            return response()->json([
                'message' => 'Expenses Calculated Successfully!',
                'year' => $year,
                'monthly_expenses' => $formattedExpenses,
            ], 200);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }
}
