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
                'type_of_tax' => 'nullable|string',
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
                'type_of_tax' => !empty($validatedData['type_of_tax']) ? $validatedData['type_of_tax'] : null,
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
                'type_of_tax' => 'nullable|string',
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
            $updateExpenses->type_of_bills = !empty($validatedData['type_of_bills']) ? $validatedData['type_of_bills'] : null;
            $updateExpenses->type_of_tax = !empty($validatedData['type_of_tax']) ? $validatedData['type_of_tax'] : null;
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
            $validCategory = ['maintenance fee', 'utility bill', 'tax', 'recurring'];

            $year = $request->input('year', Carbon::now()->year);
            $month = $request->input('month'); // If no month is provided, it's null

            if (!in_array($category, $validCategory)) {
                return response()->json([
                    'message' => 'Invalid Category',
                ], 400);
            }

            $expenseValue = [];

            if($category === 'maintenance fee' || $category === 'utility bill' || $category === 'tax'){
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
            $lastYear = $year - 1;
            $month = $request->input('month');
    
            $currentYearExpenses = Expenses::whereYear('expense_date', $year)->get();
            $lastYearExpenses = Expenses::whereYear('expense_date', $lastYear)->get();
    
            // Check if current year collection is empty
            if($currentYearExpenses->isEmpty()){
                return response()->json(['message' => 'No expenses found for current year!'], 404);
            }
    
            // Initialize the variables that will be used for filtered data
            $totalMonthlyExpenses = $currentYearExpenses;
            $totalMonthlylastYearExpenses = $lastYearExpenses;
    
            // Filter by month if specified
            if($month && $month !== 'all') {
                $totalMonthlyExpenses = $currentYearExpenses->filter(function($expense) use ($month) {
                    return Carbon::parse($expense->expense_date)->month == $month;
                });
                
                if(!$lastYearExpenses->isEmpty()) {
                    $totalMonthlylastYearExpenses = $lastYearExpenses->filter(function($expense) use ($month) {
                        return Carbon::parse($expense->expense_date)->month == $month;
                    });
                }
            }
    
            // Calculate statistics for the current year
            $totlExpensesCurrentYear = $totalMonthlyExpenses->sum('amount');
            $highestExpensesCurrentYear = $currentYearExpenses->max('amount');  
            $lowestExpensesCurrentYear = $currentYearExpenses->min('amount');
    
            // Find the months corresponding to the highest and lowest expenses for the current year
            $highestExpenseRecord = $currentYearExpenses->where('amount', $highestExpensesCurrentYear)->first();
            $lowestExpenseRecord = $currentYearExpenses->where('amount', $lowestExpensesCurrentYear)->first();
            
            $highestMonthCurrentYear = $highestExpenseRecord ? Carbon::parse($highestExpenseRecord->expense_date)->format('F') : null;
            $lowestMonthCurrentYear = $lowestExpenseRecord ? Carbon::parse($lowestExpenseRecord->expense_date)->format('F') : null;
    
            // Initialize last year statistics with null values
            $totlExpensesLastYear = null;
            $highestExpensesLastYear = null;
            $lowestExpensesLastYear = null;
            $lowestMonthLastYear = null;
            
            // Calculate statistics for the previous year only if data exists
            if(!$lastYearExpenses->isEmpty()) {
                $totlExpensesLastYear = $totalMonthlylastYearExpenses->sum('amount');
                $highestExpensesLastYear = $lastYearExpenses->max('amount');
                $lowestExpensesLastYear = $lastYearExpenses->min('amount');
                
                // Find the month for the lowest expense of last year
                $lowestExpenseLastYearRecord = $lastYearExpenses->where('amount', $lowestExpensesLastYear)->first();
                $lowestMonthLastYear = $lowestExpenseLastYearRecord ? Carbon::parse($lowestExpenseLastYearRecord->expense_date)->format('F') : null;
            }
    
            // Initialize percentage changes with null values
            $totalExpensesPercentage = 0;
            $totalExpensesChangeType = 'No Change';
            $highestExpensesPercentage = 0;
            $highestExpensesChangeType = 'No Change';
            $lowestExpensesPercentage = 0;
            $lowestExpensesChangeType = 'No Change';
    
            // Calculate percentage changes only if last year data exists and is greater than 0
            if($totlExpensesLastYear !== 0 && $totlExpensesLastYear > 0) {
                $totalExpensesPercentage = round((($totlExpensesCurrentYear - $totlExpensesLastYear) / $totlExpensesLastYear) * 100, 2);
                $totalExpensesChangeType = $totalExpensesPercentage > 0 ? 'Increase' : ($totalExpensesPercentage < 0 ? 'Decrease' : 'No Change');
            }
    
            if($highestExpensesLastYear !== 0 && $highestExpensesLastYear > 0) {
                $highestExpensesPercentage = round((($highestExpensesCurrentYear - $highestExpensesLastYear) / $highestExpensesLastYear) * 100, 2);
                $highestExpensesChangeType = $highestExpensesPercentage > 0 ? 'Increase' : ($highestExpensesPercentage < 0 ? 'Decrease' : 'No Change');
            }   
    
            if($lowestExpensesLastYear !== 0 && $lowestExpensesLastYear > 0) {
                $lowestExpensesPercentage = round((($lowestExpensesCurrentYear - $lowestExpensesLastYear) / $lowestExpensesLastYear) * 100, 2);
                $lowestExpensesChangeType = $lowestExpensesPercentage > 0 ? 'Increase' : ($lowestExpensesPercentage < 0 ? 'Decrease' : 'No Change');
            }
    
            return response()->json([
                'message' => 'Expenses Calculated Successfully!',
                'year' => $year,
                'month' => $month === 'all' ? 'All Months' : Carbon::create()->month($month)->format('F'),
                'data' => [
                    'total_expenses_current_year' => $totlExpensesCurrentYear,
                    'total_expenses_last_year' => $totlExpensesLastYear,    
                    'total_expenses_change_type' => $totalExpensesChangeType,
                    'total_expenses_change_percentage' => $totalExpensesPercentage,
                    'highest_expenses_current_year' => $highestExpensesCurrentYear,
                    'highest_month_current_year' => $highestMonthCurrentYear,
                    'lowest_expenses_current_year' => $lowestExpensesCurrentYear,
                    'lowest_month_current_year' => $lowestMonthCurrentYear,
                    'highest_expenses_last_year' => $highestExpensesLastYear,
                    'lowest_expenses_last_year' => $lowestExpensesLastYear,
                    'lowest_month_last_year' => $lowestMonthLastYear,
                    'highest_expenses_change_percentage' => $highestExpensesPercentage,
                    'highest_expenses_change_type' => $highestExpensesChangeType,
                    'lowest_expenses_change_percentage' => $lowestExpensesPercentage,
                    'lowest_expenses_change_type' => $lowestExpensesChangeType,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to calculate expenses: ' . $e->getMessage()], 500);
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
