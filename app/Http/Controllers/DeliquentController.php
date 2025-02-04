<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deliquent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class DeliquentController extends Controller
{
    public function Store_Delequent(Request $request)
    {
        try{
            $validateData = $request->validate([
                'tenant_id' => 'required|integer',
                'amount_overdue' => 'required|integer',
                'last_due_date' => 'required|date_format:m/d/Y',
                'status' => 'required|string'
            ]);

            $dueDate = Carbon::createFromFormat('m/d/Y', $validateData['last_due_date'])->format('Y-m-d');

            $existingRecord = Deliquent::where('tenant_id', $validateData['tenant_id'])
            ->whereYear('month_overdue',  Carbon::parse($dueDate)->year)
            ->whereMonth('month_overdue',  Carbon::parse($dueDate)->month)
            ->first();

            if($existingRecord){
                return response()->json([
                    'message' => 'Deliquent already exists for this tenant',
                ], 404);
            }

            Log::info('Storing Deliquent data:', [
                'tenant_id' => $validateData['tenant_id'],
                'amount_overdue' => $validateData['amount_overdue'],
                'month_overdue' => $dueDate,
                'status' => $validateData['status']
            ]);

            $data = [
                'tenant_id' => $validateData['tenant_id'],
                'amount_overdue' => $validateData['amount_overdue'],
                'month_overdue' => Carbon::createFromFormat('m/d/Y', $validateData['last_due_date'])->format('Y-m-d'),
                'status' => $validateData['status']
            ];

            $store = Deliquent::create($data);

            return response()->json([
                'message' => 'Deliquent created successfully',
                'data' => $store
            ], 201);

            


        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to store Deliquent data', 'error' => $e->getMessage()], 500);
        }
    }

    public function Get_Delequent_Details($id)
    {
        try{
            $delequent = Deliquent::where('tenant_id', $id)->get();
            
            if(!$delequent){
                return response()->json(['message' => 'No data found!'], 404);
            }

            return response()->json([
                'message' => 'Delequent details retrieved successfully',
                'data' => $delequent
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to store Deliquent data', 'error' => $e->getMessage()], 500);
        }
    }
}
