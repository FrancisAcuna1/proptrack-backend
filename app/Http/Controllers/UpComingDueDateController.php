<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\RentalAgreement;
use App\Models\RentedUnitDetails;
use App\Models\Property;
use App\Models\Apartment;
use App\Models\BoardingHouse;
use App\Models\Bed;
use App\Models\PaymentTransactions;
use App\Models\Revenue;
use App\Models\Expenses;
use Carbon\Carbon;

class UpComingDueDateController extends Controller
{
    public function getAllUnits($propid)
    {
        try {
            // Retrieve the property with associated apartments and boarding houses
            $property = Property::with(['apartments', 'boardingHouses'])->findOrFail($propid);

            // Initialize an array to store rented units
            $rentedUnits = [];

            // Loop through apartments and validate the rental agreement's unit type
            
            foreach ($property->apartments as $apartment) {
                $rentalAgreement = RentalAgreement::with('tenant', 'rentedUnit')
                    ->where('rented_unit_id', $apartment->id)
                    ->where('rented_unit_type', 'Apartment')
                    ->first();
                
                if ($rentalAgreement) {
                    // Get all payments for the same month
                    $payments = PaymentTransactions::where('tenant_id', $rentalAgreement->tenant_id)
                        ->whereIn('transaction_type', ['Rental Fee', 'Advance Payment', 'Initial Payment'])
                        ->orderBy('paid_for_month', 'desc')
                        ->get()
                        ->groupBy(function($payment) {
                            // Convert to Carbon instance before formatting
                            return Carbon::parse($payment->paid_for_month)->format('Y-m');
                        });
            
                    if ($payments->isNotEmpty()) {
                        // Get the latest month's payments
                        $latestMonth = $payments->keys()->first();
                        $latestMonthPayments = $payments[$latestMonth];
            
                        // Calculate total months covered for the latest month
                        $totalMonthsCovered = $latestMonthPayments->sum('months_covered');
            
                        // Get the payment with the latest paid_for_month
                        $lastPayment = $latestMonthPayments->first();
                        
                        // Add the total months covered to the last payment
                        if ($lastPayment) {
                            $lastPayment->months_covered = $totalMonthsCovered;
                        }
            
                        $rentedUnits[] = [
                            'unit_id' => $apartment->id,
                            'property_type' => 'Apartment',
                            'rental_agreement' => $rentalAgreement,
                            'last_payment' => $lastPayment
                        ];
                    } else {
                        // Handle case where there are no payments
                        $rentedUnits[] = [
                            'unit_id' => $apartment->id,
                            'property_type' => 'Apartment',
                            'rental_agreement' => $rentalAgreement,
                            'last_payment' => null
                        ];
                    }
                }
            }
            
            foreach ($property->boardingHouses as $boardingHouse) {
                $rentalAgreements = RentalAgreement::with('tenant', 'rentedUnit')
                    ->where('rented_unit_id', $boardingHouse->id)
                    ->where('rented_unit_type', 'Boarding House')
                    ->get();
            
                foreach ($rentalAgreements as $rentalAgreement) {
                    $tenantId = $rentalAgreement->tenant_id;
            
                    // Get all payments for the same month
                    $payments = PaymentTransactions::where('tenant_id', $tenantId)
                        ->whereIn('transaction_type', ['Rental Fee', 'Advance Payment', 'Initial Payment'])
                        ->orderBy('paid_for_month', 'desc')
                        ->get()
                        ->groupBy(function($payment) {
                            // Convert to Carbon instance before formatting
                            return Carbon::parse($payment->paid_for_month)->format('Y-m');
                        });
            
                    if ($payments->isNotEmpty()) {
                        // Get the latest month's payments
                        $latestMonth = $payments->keys()->first();
                        $latestMonthPayments = $payments[$latestMonth];
            
                        // Calculate total months covered for the latest month
                        $totalMonthsCovered = $latestMonthPayments->sum('months_covered');
            
                        // Get the payment with the latest paid_for_month
                        $lastPayment = $latestMonthPayments->first();
                        
                        // Add the total months covered to the last payment
                        if ($lastPayment) {
                            $lastPayment->months_covered = $totalMonthsCovered;
                        }
            
                        $rentedUnits[] = [
                            'unit_id' => $boardingHouse->id,
                            'property_type' => 'Boarding House',
                            'rental_agreement' => $rentalAgreement,
                            'last_payment' => $lastPayment
                        ];
                    } else {
                        // Handle case where there are no payments
                        $rentedUnits[] = [
                            'unit_id' => $boardingHouse->id,
                            'property_type' => 'Boarding House',
                            'rental_agreement' => $rentalAgreement,
                            'last_payment' => null
                        ];
                    }
                }
            }


            // Loop through boarding houses and validate the rental agreement's unit type
            // foreach ($property->boardingHouses as $boardingHouse) {
            //     $rentalAgreement = RentalAgreement::with('tenant')
            //         ->where('rented_unit_id', $boardingHouse->id)
            //         ->where('rented_unit_type', 'Boarding House')
            //         ->get();
            //     if ($rentalAgreement) {
            //         $lastPayment = PaymentTransactions::where('tenant_id', $rentalAgreement->tenant_id)
            //         ->orderBy('date', 'desc')
            //         ->first();
            //         $rentedUnits[] = [
            //             'unit_id' => $boardingHouse->id,
            //             'property_type' => 'Boarding House',
            //             'rental_agreement' => $rentalAgreement,
            //             'last_payment' => $lastPayment
            //         ];
            //     }
            // }

            return response()->json([
                'message' => 'Units and rental agreements retrieved successfully!',
                'data' => $rentedUnits
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving units or rental agreements', 'error' => $e->getMessage()], 500);
        }
    }

}
