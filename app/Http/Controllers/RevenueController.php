<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log; // Import Log at the top
use Illuminate\Http\Request;
use Illuminate\Http\validatedData;
use App\Models\Account;
use App\Models\RentalAgreement;
use App\Models\RentedUnitDetails;
use App\Models\Apartment;
use App\Models\BoardingHouse;
use App\Models\Bed;
use App\Models\PaymentTransactions;
use App\Models\Revenue;
use App\Models\Expenses;
use App\Models\Property;
use App\Models\Deliquent;
use Carbon\Carbon;



class RevenueController extends Controller
{
    // this function is for Tenant List for payment Purpose
    public function List_of_Tenant(){
        try{
            $tenants = Account::where('user_type', 'User')
            ->where('status', 'Active')
            ->get();

            return response()->json([
                'message' => 'Tenant List Retrieved Successfully',
                'data' => $tenants,
               
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }

    # this function is to get list of the payor in the units
    public function Get_Payor_List($id, $type) 
    {
        try{
            $payor = RentalAgreement::with('tenant')
            ->where('rented_unit_id', $id)
            ->where('rented_unit_type', $type)
            ->get();

            if(!$payor){
                return response()->json(['message' => 'No Payor Found'], 404);
            }

            return response()->json([
                'message' => 'Payor List Retrieved Successfully',
                'data' => $payor,
            ]);
            
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }

    // #this code is to retrive the tenant payment history
    // public function Get_Tenant_Payment_Details($id, $type) 
    // {
    //     try{
    //         $tenantPayment = PaymentTransactions::where('rented_unit_id', $id)
    //         ->where('rented_unit_type', $type)
    //         ->whereIn('transaction_type', ['Rental Fee', 'Advance Payment', 'Initial Payment'])
    //         ->get();
            
    //         if(!$tenantPayment){
    //             return response()->json(['message' => 'No Payment Details Found'], 404);
    //         }

    //         return response()->json([
    //             'message' => 'Tenant Payment Details Retrieved Successfully',
    //             'data' => $tenantPayment,
    //         ], 201);

    //     }catch (\Exception $e) {
    //         return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
    //     }
    // }
    public function Get_Tenant_Payment_Details($id, $type) 
    {
        try {
            // Retrieve all payment transactions for the given tenant and unit type
            $tenantPayment = PaymentTransactions::where('rented_unit_id', $id)
                ->where('rented_unit_type', $type)
                ->whereIn('transaction_type', ['Rental Fee', 'Advance Payment', 'Initial Payment'])
                ->get();
            
            // Check if no payment records were found
            if ($tenantPayment->isEmpty()) {
                return response()->json(['message' => 'No Payment Details Found'], 404);
            }
    
            // Initialize an array to hold combined payments by tenant
            $combinedPayments = [];
    
            foreach ($tenantPayment as $payment) {
                // Extract the date (month-day-year) for grouping
                $dateKey = date('m-d-Y', strtotime($payment->date));
    
                // Check if the tenant already exists in the combinedPayments array
                if (!isset($combinedPayments[$payment->tenant_id])) {
                    // If not, initialize an entry for this tenant
                    $combinedPayments[$payment->tenant_id] = [];
                }
    
                // Check if we already have a payment for this tenant on the same day
                if (!isset($combinedPayments[$payment->tenant_id][$dateKey])) {
                    // If it doesn't exist, add the payment details
                    $combinedPayments[$payment->tenant_id][$dateKey] = [
                        'tenant_id' => $payment->tenant_id,
                        'rented_unit_id' => $payment->rented_unit_id,
                        'rented_unit_type' => $payment->rented_unit_type,
                        'amount' => (float)$payment->amount,
                        'date' => $payment->date,
                        'paid_for_month' => $payment->paid_for_month,
                        'transaction_type' => $payment->transaction_type,
                        'months_covered' => $payment->months_covered,
                        'status' => $payment->status,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at,
                    ];
                } else {
                    // If it exists, combine the amounts and months covered
                    $combinedPayments[$payment->tenant_id][$dateKey]['amount'] += (float)$payment->amount;
                    $combinedPayments[$payment->tenant_id][$dateKey]['months_covered'] += $payment->months_covered;
                }
            }
    
            // Convert the combined payments back to an array
            $finalPayments = [];
            foreach ($combinedPayments as $tenant_id => $payments) {
                foreach ($payments as $dateKey => $payment) {
                    $finalPayments[] = $payment; // Add each combined payment
                }
            }
    
            // Return the response with combined payments
            return response()->json([
                'message' => 'Tenant Payment Details Retrieved Successfully',
                'data' => $finalPayments,
            ], 201);
    
        } catch (\Exception $e) {
            // Handle any exceptions that occur
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function Store_Payment(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'tenant_id' => 'required|integer',
                'amount' => 'required|integer|min:1',
                'payment_date' => 'required|date_format:m/d/Y',
                'transaction_type' => 'required|string',
                'status' => 'required|string',
                'months_covered' => 'nullable|integer',
                'selected_months' => 'nullable|array',
                'paid_for_month' => 'nullable|date_format:m/d/Y'
            ]);

            Log::info('validated Tenant Data:', $validatedData);

            $existing = PaymentTransactions::where('tenant_id', $validatedData['tenant_id'])
            ->where('date',Carbon::createFromFormat('m/d/Y', $validatedData['payment_date'])->format('Y-m-d'))
            ->where('transaction_type', $validatedData['transaction_type'])
            ->first();

            if($existing){
                return response()->json(['message' => 'Payment already exists for this tenant'], 409);
            }

            $rentalAgreement = RentalAgreement::with('rentedUnitDetails')->where('tenant_id', $validatedData['tenant_id'])->first();
    
            $data = [
                'tenant_id' => $validatedData['tenant_id'],
                'rented_unit_id' => $rentalAgreement->rented_unit_id,
                'rented_unit_type' => $rentalAgreement->rented_unit_type,
                'amount' => $validatedData['amount'],
                'date' => Carbon::createFromFormat('m/d/Y', $validatedData['payment_date'])->format('Y-m-d'),
                'paid_for_month' => $validatedData['paid_for_month'] ? Carbon::createFromFormat('m/d/Y', $validatedData['paid_for_month'])->format('Y-m-d') : null,
                'transaction_type' => $validatedData['transaction_type'],
                'status' => $validatedData['status'],
                'months_covered' => $validatedData['months_covered'] ?? null,
            ];

            $payment = PaymentTransactions::create($data);

            if (!empty($validatedData['selected_months'])) {
                foreach ($validatedData['selected_months'] as $months) {
                    $formattedMonth = Carbon::parse($months)->format('Y-m-d');
                    $delinquentRecord = Deliquent::where('tenant_id', $validatedData['tenant_id'])
                        ->where('month_overdue', $formattedMonth)
                        ->first();
            
                    if ($delinquentRecord) {
                        $delinquentRecord->status = "Paid";
                        $delinquentRecord->save();
                    }
                }
            } else {
                // Explicitly set selected_months to null if it's not provided
                $validatedData['selected_months'] = null;
            }

            // Now, update the revenue for the month the payment was made
            $paymentMonth = Carbon::parse($validatedData['payment_date'])->format('m');
            $paymentYear = Carbon::parse($validatedData['payment_date'])->format('Y');

            // Find the revenue record for the month and year of the payment
            $revenue = Revenue::where('month', $paymentMonth)
            ->where('year', $paymentYear)
            ->first();

            // If revenue record doesn't exist, create one
            if (!$revenue) {
                $revenue = Revenue::create([
                    'month' => $paymentMonth,
                    'year' => $paymentYear,
                    'total_amount' => 0,  // Initialize to zero
                ]);
            }

            // Update the total revenue for that month
            $revenue->total_amount += $validatedData['amount'];
            $revenue->save();

            return response()->json([
                'message' => 'Payment transaction created successfully',
                'data' => $payment,
                'revenue' => $revenue
            ], 201);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Recording Payment failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Edit_Payment($id)
    {
        try{
            $payment = PaymentTransactions::with('tenant.rentalAgreement')->where('id', $id)->get();
            if(!$payment){
                return response()->json(['message' => 'Payment transaction not found'], 404);
            }

            return response()->json([
                'message' => 'Payment transaction found',
                'data' => $payment
            ], 200);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Update_Payment(Request $request, $id)
    {
       try{
            $validatedData = $request->validate([
                'tenant_id' => 'required|integer',
                'amount' => 'required|integer|min:1',
                'payment_date' => 'required|date_format:m/d/Y',
                'transaction_type' => 'required|string',
                'status' => 'required|string',
            ]);

            $existing = PaymentTransactions::where('tenant_id', $validatedData['tenant_id'])
            ->where('date',Carbon::createFromFormat('m/d/Y', $validatedData['payment_date'])->format('Y-m-d'))
            ->where('transaction_type', $validatedData['transaction_type'])
            ->where('id', '!=', $id)
            ->first();

            if($existing){
                return response()->json(['message' => 'Payment already exists for this tenant'], 409);
            }

            $payment = PaymentTransactions::find($id);

            if (!$payment){
                return response()->json(['message' => 'Payment transaction not found'], 401);
            }

            $rentalAgreement = RentalAgreement::with('rentedUnitDetails')->where('tenant_id', $validatedData['tenant_id'])->first();



            if(!$rentalAgreement){
                return response()->json(['message' => 'Rental Agreement not found'], 401);
            }

            $originalPaymentAmount = $payment->amount;
            $originalDate = $payment->date;
            $currentPaymentAmount = $validatedData['amount'];


            $payment->tenant_id = $validatedData['tenant_id'];
            $payment->amount = $validatedData['amount'];
            $payment->date =  Carbon::createFromFormat('m/d/Y', $validatedData['payment_date'])->format('Y-m-d');
            $payment->transaction_type = $validatedData['transaction_type'];
            $payment->status = $validatedData['status'];
            $payment->rented_unit_id = $rentalAgreement->rented_unit_id;
            $payment->rented_unit_type = $rentalAgreement->rented_unit_type;

            // Get month and year for both original and new payment dates
            $originalMonth = Carbon::parse($originalDate)->format('m');
            $originalYear = Carbon::parse($originalDate)->format('Y');
            $newMonth = Carbon::parse($validatedData['payment_date'])->format('m');
            $newYear = Carbon::parse($validatedData['payment_date'])->format('Y');

            if ($originalPaymentAmount != $currentPaymentAmount && $originalYear == $newYear) {
                if($originalMonth != $newMonth){

                    $originalRevenue = Revenue::where('month', $originalMonth)
                    ->where('year', $originalYear)
                    ->first();
            
                    if ($originalRevenue) {
                        // Subtract original payment from old month
                        $originalRevenue->total_amount -= $originalPaymentAmount;
                        $originalRevenue->save();
                    }

                    $newRevenue = Revenue::where('month', $newMonth)
                    ->where('year', $newYear)
                    ->first();
                
                    if($newRevenue){
                        // Update the revenue based on the comparison of amounts
                        $newRevenue->total_amount += $currentPaymentAmount;
                        $newRevenue->save();
                    }else{
                    
                        Revenue::create([
                            'total_amount' => $currentPaymentAmount, // $150
                            'month' => $newMonth,  // 2
                            'year' => $newYear,    // 2024
                        ]);                      
                    }

                }else{
                    $originalRevenue = Revenue::where('month', $originalMonth)
                    ->where('year', $originalYear)
                    ->first();
            
                    // Check if the original revenue record exists
                    if (!$originalRevenue) {
                        return response()->json(['No Original Revenue found!'], 404);
                    }
                
                    // Update the revenue based on the comparison of amounts
                    if ($currentPaymentAmount < $originalPaymentAmount) {
                        // Current amount is less than original, subtract from revenue
                        $originalRevenue->total_amount -= ($originalPaymentAmount - $currentPaymentAmount);
                    } else {
                        // Current amount is greater than original, add to revenue
                        $originalRevenue->total_amount += ($currentPaymentAmount - $originalPaymentAmount);
                    }
                    $originalRevenue->save();
                }
                
            }

            if($originalPaymentAmount == $currentPaymentAmount && $originalYear == $newYear){
                if($originalMonth != $newMonth){

                    $originalRevenue = Revenue::where('month', $originalMonth)
                    ->where('year', $originalYear)
                    ->first();
            
                    if ($originalRevenue) {
                        // Subtract original payment from old month
                        $originalRevenue->total_amount -= $originalPaymentAmount;
                        $originalRevenue->save();
                    }

                    $newRevenue = Revenue::where('month', $newMonth)
                    ->where('year', $newYear)
                    ->first();
                
                    if($newRevenue){
                        // Update the revenue based on the comparison of amounts
                        $newRevenue->total_amount += $currentPaymentAmount;
                        $newRevenue->save();
                    }else{
                    
                        Revenue::create([
                            'total_amount' => $currentPaymentAmount, // $150
                            'month' => $newMonth,  // 2
                            'year' => $newYear,    // 2024
                        ]);                      
                    }
                }
            }

            if($originalPaymentAmount != $currentPaymentAmount && $originalYear != $newYear){
                if($originalMonth != $newMonth){
                    $originalRevenue = Revenue::where('month', $originalMonth)
                    ->where('year', $originalYear)
                    ->first();

                    if ($originalRevenue) {
                        // Subtract original payment from old month
                        $originalRevenue->total_amount -= $originalPaymentAmount;
                        $originalRevenue->save();
                    }

                    $newRevenue = Revenue::where('month', $newMonth)
                    ->where('year', $newYear)
                    ->first();
                
                    if($newRevenue){
                        // Update the revenue based on the comparison of amounts
                        $newRevenue->total_amount += $currentPaymentAmount;
                        $newRevenue->save();
                    }else{
                    
                        Revenue::create([
                            'total_amount' => $currentPaymentAmount, // $150
                            'month' => $newMonth,  // 2
                            'year' => $newYear,    // 2024
                        ]);                      
                    }
                }
            }

            
            if($originalPaymentAmount == $currentPaymentAmount && $originalYear != $newYear){
                if($originalMonth != $newMonth){
                    $originalRevenue = Revenue::where('month', $originalMonth)
                    ->where('year', $originalYear)
                    ->first();

                    if ($originalRevenue) {
                        // Subtract original payment from old month
                        $originalRevenue->total_amount -= $originalPaymentAmount;
                        $originalRevenue->save();
                    }

                    $newRevenue = Revenue::where('month', $newMonth)
                    ->where('year', $newYear)
                    ->first();
                
                    if($newRevenue){
                        // Update the revenue based on the comparison of amounts
                        $newRevenue->total_amount += $currentPaymentAmount;
                        $newRevenue->save();
                    }else{
                    
                        Revenue::create([
                            'total_amount' => $currentPaymentAmount, // $150
                            'month' => $newMonth,  // 2
                            'year' => $newYear,    // 2024
                        ]);                      
                    }
                }
            }

            $payment->save();

            return response()->json([
                'message' => 'Payment transaction updated successfully!',
                'data' => $payment
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update payment!', 'error' => $e->getMessage()], 500);
        }

    }

    public function Delete_Payment($id)
    {
        try{
            $delete = PaymentTransactions::where('id', $id)->first();

            if(!$delete){
                return response()->json(['message' => 'No Payment Transaction Found!'], 404);
            }

            // Get the payment's month and year
            $paymentMonth = Carbon::parse($delete->date)->format('m');
            $paymentYear = Carbon::parse($delete->date)->format('Y');
            $paymentAmount = $delete->amount;

              // Find the corresponding revenue for the month and year
            $revenue = Revenue::where('month', $paymentMonth)
            ->where('year', $paymentYear)
            ->first();
            
            if($revenue){
                $revenue->total_amount  -= $paymentAmount;
                $revenue->save();
                // if($revenue->total_amount <= 0){
                //     $revenue->delete();
                // }else{
                    
                // }
            }

            // Actually delete the record
            $delete->delete();

            return response()->json([
                'message' => 'Payment Deleted Successfully!',
                'data' => $delete,
                'revnue' => $revenue
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Delete Payment!', 'error' => $e->getMessage()], 500);
        }
    }

    // this code is to display all payment 
    public function Payment_Details(Request $request)
    {
        try{
            $year = $request->input('year', Carbon::now()->year);
            $month = $request->input('month'); 

            $queryPayment = PaymentTransactions::with('tenant.rentalAgreement.rentedUnit')->whereYear('date', $year);

            if($month && $month !== 'all'){
                $queryPayment->whereMonth('date', $month);
            }

            $allPayment = $queryPayment->get();

            if ($allPayment->isEmpty()) {
                return response()->json(['message' => 'No payment found for the specified filters!']);
            }

            return response()->json([
                'message' => 'Payment details retrieved successfully',
                'data' => $allPayment
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Filter_Payment(Request $request, $category)
    {
        try{
            $validCategory = ['Initial Payment', 'Advance Payment', 'Security Deposit', 'Rental Fee', 'Penalties', 'Extra Amenities', 'Damage Compensation', 'Replacement Fee'];

             // Get the year and month from the request (optional, default to current year/month)
            $year = $request->input('year', Carbon::now()->year);
            $month = $request->input('month'); // If no month is provided, it's null


            if (!in_array($category, $validCategory)) {
                return response()->json([
                    'message' => 'Invalid Category',
                ], 400);
            }
            $query = PaymentTransactions::with('tenant.rentalAgreement.rentedUnit')
            ->where('transaction_type', $category)
            ->whereYear('date', $year);

            if($month && $month !== 'all'){
                $query->whereMonth('date', $month);
            }

            $filter = $query->get();

            //    // Check if any data is returned
            if ($filter->isEmpty()) {
                return response()->json([
                    'message' => 'No payment found for the specified filters!'
                ]);
            }
            
            return response()->json([
                'message' => 'Payment details retrieved successfully',
                'data' => $filter, 
                'year' => $year,
                'month' => $month
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Calculate_Income(Request $request)
    {
        try {
            // Get the year from the request (optional, defaults to current year)
            $year = $request->input('year', Carbon::now()->year);
            $month = $request->input('month');

            // Query builder to filter by transaction type, year, and optionally by month
            $queryInitialPayment = PaymentTransactions::where('transaction_type', 'Initial Payment')
            ->whereYear('date', $year);
            $queryRentalFee = PaymentTransactions::where('transaction_type', 'Rental Fee')
            ->whereYear('date', $year);
            $queryAdvancePayment = PaymentTransactions::where('transaction_type', 'Advance Payment')
            ->whereYear('date', $year);
            $querySecurityDeposit = PaymentTransactions::where('transaction_type', 'Security Deposit')
            ->whereYear('date', $year);
            $queryOtherIncome = PaymentTransactions::whereIn('transaction_type', ['Penalties', 'Extra Amenities', 'Damage Compensation', 'Replacement Fee'])
            ->whereYear('date', $year);

            if($month && $month !== 'all'){
                $queryInitialPayment = $queryInitialPayment->whereMonth('date', $month);
                $queryRentalFee = $queryRentalFee->whereMonth('date', $month);
                $queryAdvancePayment = $queryAdvancePayment->whereMonth('date', $month);
                $queryOtherIncome = $queryOtherIncome->whereMonth('date', $month);
                $querySecurityDeposit = $querySecurityDeposit->whereMonth('date', $month);
            }

            // Sum the amounts based on filtered results
            $totalInitialPayment = $queryInitialPayment->sum('amount');
            $totalRentalFee = $queryRentalFee->sum('amount');
            $totalAdvancePayment = $queryAdvancePayment->sum('amount');
            $totalOtherIncome = $queryOtherIncome->sum('amount');
            $totalSecurityDeposit = $querySecurityDeposit->sum('amount');


            #start this code was a new 

            //query all revenue current
            $queryRevenues = Revenue::query();
            $queryRevenues = $queryRevenues->where('year', $year); 

            //query all expenses current
            $queryExpenses = Expenses::query();
            $queryExpenses = $queryExpenses->whereYear('expense_date', $year);

     
            if ($month && $month !== 'all') {
                $queryRevenues = $queryRevenues->where('month', $month);
                $queryExpenses = $queryExpenses->whereMonth('expense_date', $month);
            }

            $revenues = $queryRevenues->get();
            $expenses = $queryExpenses->get();

            $totalExpenses = $expenses->sum('amount'); // current expenses
            $totalIncome = $revenues->sum('total_amount'); // current income
            $totalRevenue = $totalIncome - $totalExpenses;


            // Previous income and expenses logic
            $prevTotalIncome = 0;
            $prevTotalExpenses = 0;
            $previousDate = null;
            if ($month && $month !== 'all') {
                // Calculate for the previous month 
                $previousDate = Carbon::createFromDate($year, $month, 1)->subMonth();
                $prevTotalIncome = Revenue::where('year', $previousDate->year)
                    ->where('month', $previousDate->month)
                    ->sum('total_amount');
                $prevTotalExpenses = Expenses::whereYear('expense_date', $previousDate->year)
                    ->whereMonth('expense_date', $previousDate->month)
                    ->sum('amount');
            } else {
                // Calculate for the previous year
                $prevTotalIncome = Revenue::where('year', $year - 1)->sum('total_amount');
                $prevTotalExpenses = Expenses::whereYear('expense_date', $year - 1)->sum('amount');
            }

            $prevNetIncome = $prevTotalIncome - $prevTotalExpenses; 

            $percentageChange = 'No Change'; // Track whether the percentage change is an increase or decrease
            if($prevNetIncome > 0){
                $totalIncomePercentage = round((($totalRevenue - $prevNetIncome ) / $prevNetIncome) * 100, 2);
                if($totalIncomePercentage > 0){
                    $percentageChange = 'Increase';
                }else{
                    $percentageChange = 'Decrease';
                }
            }else{
                $totalIncomePercentage = 0;
            }

            return response()->json([
                'message' => 'Income details retrieved successfully',
                'year' => $year,
                'month' => $month ? $month : 'All Months',
                'total_income' => [
                    'initial_payment' => $totalInitialPayment,
                    'rental_fee' => $totalRentalFee,
                    'advance_payment' => $totalAdvancePayment,
                    'other_income' => $totalOtherIncome,
                    'securityDeposit' => $totalSecurityDeposit
                ],
                'total' => $totalRevenue,
                'totalIncome' => $totalIncome,
                'totalExpenses' => $totalExpenses,
                'previous' => [
                    'income' => $prevTotalIncome,
                    'expenses' => $prevTotalExpenses,
                    'net_income' => $prevNetIncome,
                ],
                'percentageChangeType' => $percentageChange,
                'percentage' => $totalIncomePercentage,
                // 'prevYear' => $prevYear,
                'date' => $previousDate  
                // 'total_income_by_month' => $totalIncomeForMonth,
                // 'total_income_by_year' => $totalIncomeByYear,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }


    public function Income_Statistic(Request $request)
    {
        try{
            $year = $request->input('year', Carbon::now()->year); // Selected year or default to current year
            $lastYear = $year - 1; // Subtract 1 to get the previous year

            
            $currentYearIncome = Revenue::where('year', $year)->get();
            $lastYearIncome = Revenue::where('year', $lastYear)->get();
            
            if(!$currentYearIncome){
                return response()->json(['message' => 'No income found!'], 404);
            }

            if(!$lastYearIncome){
                return response()->json(['message' => 'No income found!'], 404);
            }

             // Calculate statistics for the current year
            $highestIncomeCurrentYear = $currentYearIncome->max('total_amount');
            $lowestIncomeCurrentYear = $currentYearIncome->min('total_amount');

             // Find the months corresponding to the highest and lowest income for the current year
            $highestMonthCurrentYear = $currentYearIncome->where('total_amount', $highestIncomeCurrentYear)->first()?->month ?? null;
            $lowestMonthCurrentYear = $currentYearIncome->where('total_amount', $lowestIncomeCurrentYear)->first()?->month ?? null;

            // Calculate statistics for the previous year
            $highestIncomeLastYear = $lastYearIncome->max('total_amount');
            $lowestIncomeLastYear = $lastYearIncome->min('total_amount');

            $highestIncomeChangeType = 'No Change'; // Track whether the percentage change is an increase or decrease
            $lowestIncomeChangeType = 'No Change';

            if($highestIncomeLastYear > 0){
                $highestIncomePercantage = round((($highestIncomeCurrentYear - $highestIncomeLastYear) / $highestIncomeLastYear) * 100, 2);
                if($highestIncomePercantage > 0){
                    $highestIncomeChangeType = 'Increase';
                }else{
                    $highestIncomeChangeType = 'Decrease';
                }
            }else{
                $highestIncomePercantage = 0;
            }


            if($lowestIncomeLastYear > 0){
                $lowestIncomePercantage = round((($lowestIncomeCurrentYear - $lowestIncomeLastYear) / $lowestIncomeLastYear) * 100, 2);
                if($lowestIncomePercantage > 0){
                    $lowestIncomeChangeType = 'Increase';
                }else{
                    $lowestIncomeChangeType = 'Decrease';
                }
            }else{
                $lowestIncomePercantage = 0;
            }

            $data = [
                'currentYear' => [
                    'highestIncome' => $highestIncomeCurrentYear,
                    'lowestIncome' => $lowestIncomeCurrentYear,
                ],
                'lastYear' => [
                    'highestIncome' => $highestIncomeLastYear,
                    'lowestIncome' => $lowestIncomeLastYear,
                ],
                'highestIncomeChange' => [
                    'percentage' => $highestIncomePercantage,
                    'type' => $highestIncomeChangeType,
                ],
                'lowestIncomeChange' => [
                    'percentage' => $lowestIncomePercantage,
                    'type' => $lowestIncomeChangeType,
                ],
                'months' => [
                    'highestMonths' => $highestMonthCurrentYear,
                    'lowestMonths' => $lowestMonthCurrentYear,
                ]
                
            ];
            // $data = [
            //     'highestIncome' => $queryIncome->max('total_amount'),
            //     'lowestIncome' => $queryIncome->min('total_amount'),
            //     'averageIncome' => $queryIncome->avg('total_amount')
            // ];
   

            return response()->json([
                'message' => 'Income stats retrieved successfully',
                'year' => $currentYearIncome,
                'last' => $lastYear,
                'data' => $data
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Query Data failed', 'error' => $e->getMessage()], 500);
        }
    }


    public function Paid_by_Deposit($tenatId)
    {
        try{
            $paidByDeposit = RentalAgreement::where('tenant_id', $tenatId)->get();
                 // Check if any rental agreements were found
            if ($paidByDeposit->isEmpty()) {
                return response()->json([
                    'message' => 'No Deposit Found!'
                ], 404);
            }
            // Iterate over each rental agreement and update the is_last_month field
            foreach ($paidByDeposit as $agreement) {
                $agreement->is_last_month = true;
                $agreement->save();
            }
            return response()->json([
                'message' => 'Securit Deposit Successfully Used',
                'data' => $paidByDeposit,
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to use deposit', 'error' => $e->getMessage()], 500);
        } 
    }

    


}
