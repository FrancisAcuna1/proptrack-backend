<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\validatedData;
use App\Models\RecurringExpenses;
use App\Models\Expenses;
use Carbon\Carbon;



class RecurringExpensesController extends Controller
{
    public function Get_RecurringExpenses_Details(Request $request) 
    {
        try{
            $year = $request->input('year', Carbon::now()->year);
            $month = $request->input('month'); 

            $recurring = Expenses::with('unit')->where('recurring', true)->whereYear('expense_date', $year);

            if(!$recurring){
                return response()->json(['message' => 'No Recurring Expenses Found'], 404);
            }

            if($month && $month !== "all"){
              $recurring->whereMonth('expense_date', $month);
            }

            $recurringDetails = $recurring->get();

            return response()->json([
                'message' => 'Successfully query the data',
                'data' => $recurringDetails
            ], 200);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Store Recurring Expenses', 'error' => $e->getMessage()], 500);
        }
    }

    public function Filter_Recurring_Expenses(Request $request, $category, $validBillType)
    {
        try{
            $validBillType = ['water bill', 'electric bill', 'wifi'];
            $validCategory = ['maintenance', 'utility'];

            $year = $request->input('year', Carbon::now()->year);
            $month = $request->input('month'); 

            $validOptions = array_merge($validCategory, $validBillType);
            if (!in_array($category, $validOptions)) {
                return response()->json([
                    'message' => 'Invalid Category',
                ], 400);
            }

            $filter = Expenses::where('category', $validCategory)
            ->where('type_of_bills')
            ->whereYear('expense_date', $year);

            if($month && $month !== 'all'){
                $filter->whereMonth('expense_date', $month);
            }

            $filterDetails = $filter->get();
  
            return response()->json([
                'message' => 'Successfully query the data',
                'data' => $filterDetails
            ], 200);



        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Filter Recurring Expenses', 'error' => $e->getMessage()], 500);
        }
    }

    public function Paid_Recurring_Expenses($id){
        try{
            $recurring = Expenses::find($id);

            if(!$recurring){
                return response()->json([
                    'message' => 'Recurring Expenses Not Found',
                ], 404);
            }

            $recurring->status = 'Paid';
            $recurring->save();

            return response()->json([
                'message' => 'Recurring Expenses Marked as Paid',
                'data' => $recurring
            ], 201);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Query Recurring Expenses', 'error' => $e->getMessage()], 500);
        }
    }

    public function Delete_Recurring_Expenses($id)
    {
        try{
            $deleteRecurring = expenses::find($id);

            if(!$deleteRecurring){
                return response()->json(['message' => 'No Recurring Expenses Found!'], 404);
            }

            $deleteRecurring->delete();

            return response()->json([
                'message' => 'Recurring Expenses Deleted Successfully',
            ], 200);

            
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Query Recurring Expenses', 'error' => $e->getMessage()], 500);
        }
    }

    public function Edit_Recurring($id)
    {
        try{
            $recurring = Expenses::find($id);

            if(!$recurring){
                return response()->json(['message' => 'No Recurring Expenses Found!'], 404);
            }

            return response()->json([
                'message' => 'Recurring Expenses Found',
                'data' => $recurring
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Query Recurring Expenses', 'error' => $e->getMessage()], 500);
        }
    }

    public function Update_Recurring(Request $request, $id){
        try{
            $validatedData = $request->validate([
                'amount' => 'required|numeric|min:0',
            ]);

            $recurring = Expenses::find($request->id);
            if(!$recurring){
                return response()->json(['message' => 'No Recurring Expenses Found!'], 404);
            }

            $recurring->amount = $validatedData['amount'];
            $recurring->save();

            return response()->json([
                'message' => 'Recurring Expenses Updated Successfully',
                'data' => $recurring
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Update Recurring Expenses', 'error' => $e->getMessage()], 500);
        }
    }


    public function Generate_Recurring_Expenses(Request $request)
    {
        try{
            $validatedData = request()->validate([
                'unitId' => 'required|integer',
                'type' => 'required|string',
                'category' => 'required|string',
                'type_of_bills' => 'nullable|string',
                'description' => 'required|string',
                'amount' => 'required|numeric|min:0',
                'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly',
                'startDate' => 'required|date_format:m/d/Y',
                'endDate' => 'nullable|date_format:m/d/Y|after:startDate',
                'includeWeekends' => 'nullable|boolean',
            ]);
    
            $startDate = Carbon::createFromFormat('m/d/Y', $validatedData['startDate']);
            $endDate = $validatedData['endDate'] 
                ? Carbon::createFromFormat('m/d/Y', $validatedData['endDate']) 
                : null;
            
            $includeWeekends = $validatedData['includeWeekends'] ?? false;

            $generatedExpenses = [];
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {    
                $existingExpense = Expenses::where('unit_id', $validatedData['unitId'])
                ->where('category', $validatedData['category'])
                ->when(
                    $validatedData['category'] === 'utility bill',
                    function ($query) use ($validatedData) {
                        return $query->where('type_of_bills', $validatedData['type_of_bills']);
                    }
                )
                ->whereDate('expense_date', $currentDate)
                ->where('frequency', $validatedData['frequency'])
                ->first();

                if ($existingExpense) {
                    $currentDate = $this->calculateNextDate($currentDate, $validatedData['frequency']);
                    return response()->json([
                        'message' => 'Duplicate Recurring Expenses Found! The item will be skipped.'
                    ], 409);
                    continue;
                  
                }
           
                if(!$includeWeekends && ($currentDate->isWeekend())){
                    $currentDate = $this->calculateNextDate($currentDate, $validatedData['frequency']);
                    continue;
                }

                $expense = Expenses::create([
                    'unit_id' => $validatedData['unitId'],
                    'unit_type' => $validatedData['type'],
                    'category' => $validatedData['category'],
                    'type_of_bills' => empty($validatedData['type_of_bills']) ? null : $validatedData['type_of_bills'],
                    'description' => $validatedData['description'],
                    'amount' => $validatedData['amount'],
                    'expense_date' => $currentDate,
                    'frequency' => $validatedData['frequency'],
                    'recurring' => 1,
                    'status' => 'Not paid'
                ]);

                $generatedExpenses[] = $expense;
                $currentDate = $this->calculateNextDate($currentDate, $validatedData['frequency']);
            }
            

            return response()->json([
                'message' => 'Recurring expense created successfully',
                'data' => $generatedExpenses
            ], 201);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Store Recurring Expenses', 'error' => $e->getMessage()], 500);
        }
    }

    private function calculateNextDate(Carbon $currentDate, string $frequency): Carbon
    {
        return match($frequency) {
            'daily' => $currentDate->addDay(),
            'weekly' => $currentDate->addWeek(),
            'monthly' => $currentDate->addMonth(),
            'quarterly' => $currentDate->addMonths(3),
            'yearly' => $currentDate->addYear(),
            default => throw new \InvalidArgumentException('Invalid frequency')
        };
    }

}
