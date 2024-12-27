<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\RentalAgreement;
use App\Models\Apartment;
use App\Models\BoardingHouse;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceImages;
use App\Models\ScheduleMaintenance;
use Carbon\Carbon;
use App\Mail\Notification;  
use App\Mail\RejectedNotification;  
use App\Notifications\NewNotifications; 
use Mail;   

class MaintenanceController extends Controller
{
    // Request Maintenance Module -function
    public function Tenant_Unit_Information($id)
    {
        try{
            $unitInformation = RentalAgreement::with('rentedUnit.inclusions.equipment')->where('tenant_id', $id)->first();
            if(!$unitInformation){
                return response()->json(['message' => 'No Data Found'], 404);
            }
            return response()->json([
                'message' => 'Query Data Success',
                'data' => $unitInformation
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Query failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Request_Maintenance(Request $request)
    {
        try{
            \Log::info('Incoming request data Request Maintenance:', $request->all());
            $validatedData = $request->validate([
                'tenant_id' => 'required|integer',
                'otherissues' => 'nullable|string|min:1',
                'reported_issue' => 'nullable|string',
                'status' => 'required|string',
                'issue_description' => 'required|string|min:1',
                'date_reported' => 'required|date_format:m/d/Y',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:3050',
                'unitName' => 'nullable|string'
                // 'is_schedule' => 'nullable|boolean',
                //'unit_type' => 'required|string',
            ]);

            $existing = MaintenanceRequest::where('tenant_id', $validatedData['tenant_id'])
            ->where(function ($query) use ($validatedData) {
                if (!empty($validatedData['reported_issue'])) {
                    $query->where('reported_issue', $validatedData['reported_issue']);
                }
                if (!empty($validatedData['otherissues'])) {
                    $query->orWhere('other_issue', $validatedData['otherissues']);
                }
            })
            ->first();

            if ($existing) {
                $existingSchedule = ScheduleMaintenance::where('maintenance_request_id', $existing->id)->first();
                // Check for duplicate conditions
                if ($existing->status === 'Pending' || ($existing->status === 'Accepted' && (!$existingSchedule || $existingSchedule->status !== 'Completed'))) {
                    return response()->json(['message' => 'You have already reported this issue, and it is still pending or not completed.'], 409);
                }
            }

            $data = ([
                'tenant_id' => $validatedData['tenant_id'],
                'other_issue' => $validatedData['otherissues'],
                'reported_issue' => $validatedData['reported_issue'],
                'status' => $validatedData['status'],
                // 'unit_type' => $validatedData['unit_type'],
                'issue_description' => $validatedData['issue_description'],
                'is_schedule' => false,
                'date_reported' => Carbon::createFromFormat('m/d/Y', $validatedData['date_reported'])->format('Y-m-d'),
            ]);

            $maintenance = MaintenanceRequest::create($data);
     
            // $unitName = $unit->
            // Handle multiple images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $fileName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
                    $imageFile->move(public_path('MaintenanceImages'), $fileName);

                    // Create image record in maintenance_images table
                    MaintenanceImages::create([
                        'image_path' => $fileName,
                        'maintenance_id' => $maintenance->id,
                    ]);
                }
            } else {
                MaintenanceImages::create([
                    'image_path' => null,
                    'maintenance_id' => null,
                ]);
            }


            $unitName = $validatedData['unitName']; 
            
            \Log::info('Unit Name:', ['unitName' => $unitName]);
            
            $admin = Account::where('user_type', 'Landlord')->first(); // Fetch the single landlord
            if ($admin) {
                // Send notification to the landlord
                $admin->notify(new NewNotifications($maintenance, $unitName, 'Landlord'));
            }

            return response()->json([
               'message' => 'Request Maintenance Successfully Created!',
               'data' => $maintenance
            ], 202);

            
            
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Create Maintenance', 'error' => $e->getMessage()], 500);
        }
    }

    public function Maintenance_Request_List()
    {
        try{
            $maintenance = MaintenanceRequest::with('tenant.rentalAgreement.rentedUnit')->get();

            if(!$maintenance){
                return response()->json([
                    'message' => 'No Maintenance Found',
                ]);
            }

            return response()->json([
                'message' => 'Query Successfully Created',
                'data' => $maintenance
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Query failed', 'error' => $e->getMessage()], 500);
        }
    }

    //accept maintenance request
    public function Accept_Maintenance_Request($id)
    {
        try{
            $maintenanceRequest = MaintenanceRequest::with('tenant')->findOrfail($id);

            if(!$maintenanceRequest){
                return response()->json([
                    'message' => 'No maintenance request found!'
                ]);
            }

            $maintenanceRequest->status = 'Accepted';
            $maintenanceRequest->save();

            // $tenantEmail = $maintenanceRequest->tenant->email;

            // if($tenantEmail){
            //     $mailData = [
            //         'firstname' => $maintenanceRequest->tenant->firstname,
            //         'lastname' => $maintenanceRequest->tenant->lastname,
            //     ];
            //     Mail::to($tenantEmail)->send(new Notification($mailData));
            // }

            $tenant = $maintenanceRequest->tenant;

            if ($tenant && !empty($tenant->email)) {
                // Send email notification
                $mailData = [
                    'firstname' => $tenant->firstname,
                    'lastname' => $tenant->lastname,
                ];
                Mail::to($tenant->email)->send(new Notification($mailData));
    
                // Send database notification
                $tenant->notify(new NewNotifications($maintenanceRequest, 'User'));
            } else {
                Log::warning('Tenant or tenant email is missing for maintenance request ID: ' . $id);
            }

            return response()->json([
                'message' => 'Request Maintenance Accepted Successfully!',
                'data' => $maintenanceRequest,
                
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Accept Maintenance Request', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function Reject_Maintenance_Request(Request $request, $id)
    {
        try{
            $validatedData = $request->validate([
                'remarks' => 'required|min:1|max:16500',
            ]);
            $maintenanceRequest = MaintenanceRequest::with('tenant')->findOrfail($id);

            if(!$maintenanceRequest){
                return response()->json([
                    'message' => 'No maintenance request found!'
                ]);
            }

            $maintenanceRequest->status = 'Rejected';
            $maintenanceRequest->remarks = $validatedData['remarks']?? null;
            $maintenanceRequest->save();

            $tenant = $maintenanceRequest->tenant;

            if ($tenant && !empty($tenant->email)) {
                // Send email notification
                $mailData = [
                    'firstname' => $tenant->firstname,
                    'lastname' => $tenant->lastname,
                ];
                Mail::to($tenant->email)->send(new RejectedNotification($mailData));
    
                // Send database notification
                $tenant->notify(new NewNotifications($maintenanceRequest, 'User'));
            } else {
                Log::warning('Tenant or tenant email is missing for maintenance request ID: ' . $id);
            }

            return response()->json([
                'message' => 'Request Maintenance Rejected Successfully!',
                'data' => $maintenanceRequest
                
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to reject maintenance', 'error' => $e->getMessage()], 500);
        }
    }

    public function Cancel_Request(Request $request, $id){
        try{
            $validatedData = $request->validate([
                'remarks' => 'required|min:1|max:16500',
            ]);

            $maintenanceRequest = MaintenanceRequest::with('tenant')->findOrfail($id);
            if(!$maintenanceRequest){
                return response()->json([
                    'message' => 'No maintenance request found!'
                ]);
            }

            $maintenanceRequest->status = 'Cancelled';
            $maintenanceRequest->remarks = $validatedData['remarks']?? null;
            $maintenanceRequest->save();

            $landlord = Account::where('user_type', 'Landlord')->first();

            if ($landlord) {
                // Notify the landlord with the cancellation info
                $landlord->notify(new NewNotifications($maintenanceRequest, 'Landlord'));
            } else {
                return response()->json([
                    'message' => 'No landlord found to notify!'
                ], 404);
            }
            return response()->json([
                'message' => 'Maintenance request has been successfully cancelled!',
                 'data' => $maintenanceRequest
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Cancel maintenance', 'error' => $e->getMessage()], 500);
        }
    }

    public function Edit_Request($id)
    {
        try{
            $request = MaintenanceRequest::with('maintenanceImages')->where('id', $id)->first();
            if(!$request){
                return response()->json([
                    'message' => 'No data found!'
                ], 204);
            }
            return response()->json([
                'message' => 'Maintenance request found!',
                'data' => $request
            ], 200);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Retrieve maintenance', 'error' => $e->getMessage()], 500);
        }
    }

    public function Update_Request(Request $request, $id)
    {
        try{    
            \Log::info('Incoming request data:', $request->all());
            \Log::info('Edit Id:', ['id' => $id]);
            $validatedData = $request->validate([
                'tenant_id' => 'required|integer',
                'otherissues' => 'nullable|string|min:1',
                'reported_issue' => 'nullable|string',
                'status' => 'required|string',
                'issue_description' => 'required|string|min:1',
                'date_reported' => 'required|date_format:m/d/Y',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:3050',
                 'remove_images.*' => 'nullable|integer'
                // 'is_schedule' => 'nullable|boolean',
                //'unit_type' => 'required|string',
            ]);
           
            $update = MaintenanceRequest::find($id);
            if(!$update){
                return response()->json([
                    'message' => 'no data found!'
                ], 409);
            }
            $update->tenant_id = $validatedData['tenant_id'];
            $update->other_issue = !empty($validatedData['otherissues']) ? $validatedData['otherissues'] : null;
            $update->reported_issue = !empty($validatedData['reported_issue']) ? $validatedData['reported_issue'] : null;
            $update->status = $validatedData['status'];
            $update->issue_description = $validatedData['issue_description'];
            $update->date_reported =  Carbon::createFromFormat('m/d/Y', $validatedData['date_reported'])->format('Y-m-d');
            $update->is_schedule = false;
            $update->save();
             // Remove old images if specified
            if (!empty($validatedData['remove_images'])) {
                $imagesToRemove = $update->maintenanceImages()
                    ->whereIn('id', $validatedData['remove_images'])
                    ->get();
            
                foreach ($imagesToRemove as $image) {
                    $imagePath = public_path('MaintenanceImages/' . $image->image_path);
            
                    if (is_file($imagePath) && file_exists($imagePath)) {
                        unlink($imagePath); // Delete the file only if it exists and is a valid file
                    }
            
                    // Delete the image record from the database
                    $image->delete();
                }
            }
    
            // Add new images
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                foreach ($images as $imageFile) {
                    $imageName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
                    $imageFile->move(public_path('MaintenanceImages'), $imageName);
                    $update->maintenanceImages()->create([
                        'image_path' => $imageName,
                        'maintenance_request_id' => $update->id,
                    ]);
                }
            }
            return response()->json([
                'message' => 'Request Maintenance Successfully Updated!',
                'data' => $update
            ], 201);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Update Maintenance', 'error' => $e->getMessage()], 500);
        }
    }

    // View Maitenance Request
    public function ViewRequest($id)
    {
        try{
            $view = MaintenanceRequest::with('tenant', 'maintenanceImages')->where('id', $id)->first();

            if(!$view){
                return response()->json(['message', 'No Request Found']);
            }

            return response()->json([
                'message' => 'Maintenance Request Viewed Successfully!',
                'data' => $view
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Query failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Get_Accepted_Request()
    {
        try{
            $acceptedRequests = MaintenanceRequest::with('tenant', 'maintenanceImages')->where('status', 'Accepted')->get();
            if(!$acceptedRequests){
                return response()->json(['message', 'No Request Found']);
            }

            return response()->json([
                'message' => 'Successfully query the data',
                'data' => $acceptedRequests
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Query failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function Add_Schedule(Request $request){
        try{
            $validatedData = $request->validate([
                'maintenance_id' => 'nullable|integer',
                'title' => 'required|string|min:1',
                'unitId' => 'nullable|integer',
                'type' => 'nullable|string|in:Apartment,Boarding House',
                'maintenance_task' => 'nullable|string',
                'description' => 'nullable|string|max:65500',
                'start_date' =>  'required|date_format:m/d/Y',
                'end_date' =>  'required|date_format:m/d/Y',
                'status' => 'required|string',
                'text_color' => 'required|string',   
                'bg_color' => 'required|string',
                'is_reported' => 'nullable|boolean'
                // 'estamated amount' => 'required|integer',
            ]);
    
            $data = [
                'maintenance_request_id' => $validatedData['maintenance_id'] ?? null,
                'schedule_title' => $validatedData['title'],
                'start_date' =>   Carbon::createFromFormat('m/d/Y', $validatedData['start_date'])->format('Y-m-d'),
                'end_date' => Carbon::createFromFormat('m/d/Y', $validatedData['end_date'])->format('Y-m-d'),
                'status' => $validatedData['status'],
                'text_color' => $validatedData['text_color'],
                'bg_color' => $validatedData['bg_color'],
                'is_reported_issue' => $validatedData['is_reported'],
            
                // this is for manual
                'unit_id' => $validatedData['unitId'] ?? null,
                'unit_type' => $validatedData['type'] ?? null,
                'maintenance_task' => $validatedData['maintenance_task'] ?? null,
                'description' => $validatedData['description'] ?? null,
               
            ];

            $schedule = ScheduleMaintenance::create($data);

            if(!empty($validatedData['maintenance_id'])){
                $maintenance = MaintenanceRequest::where('id', $validatedData['maintenance_id'])->first();
                if ($maintenance) {
                    $maintenance->is_schedule = true;  // Set is_schedule to true
                    $maintenance->save();  // Save the updated maintenance request
                }
            }

            return response()->json([
                'message' => 'Schedule Added Successfully!',
                'data' => $schedule
            ]);
    
            
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Create schedule maintenance', 'error' => $e->getMessage()], 500);
        }
    }

    public function Get_Schedule_Maintenance()
    {
        try{
            $scheduledMaintenance = ScheduleMaintenance::with('maintenanceRequest')->get();
            if(!$scheduledMaintenance){
                return response()->json(['message' => 'Schedule not found']);
            }
            return response()->json([
                'message' => 'Scheduled Maintenance', 
                'data' => $scheduledMaintenance
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Query Scheduled Maintenance', 'error' => $e->getMessage()], 500);
        }
    }

    public function Edit_Schedule($id)
    {
        try{
            $editSched = ScheduleMaintenance::with('maintenanceRequest')->where('id', $id)->first();
            if(!$editSched){
                return response()->json(['message' => 'Schedule not found']);
            }
            return response()->json([
                'message' => 'Schedule Found',
                'data' => $editSched
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Query Scheduled Maintenance', 'error' => $e->getMessage()], 500);
        }
    }

    public function Update_Schedule(Request $request, $id)
    {
        try{
            $validatedData = $request->validate([
                'maintenance_id' => 'nullable|integer',
                'title' => 'required|string|min:1',
                'unitId' => 'nullable|integer',
                'type' => 'nullable|string|in:Apartment,Boarding House',
                'maintenance_task' => 'nullable|string',
                'description' => 'nullable|string|max:65500',
                'start_date' =>  'required|date_format:m/d/Y',
                'end_date' =>  'required|date_format:m/d/Y',
                'status' => 'required|string',
                'text_color' => 'required|string',   
                'bg_color' => 'required|string',
                'is_reported' => 'nullable|boolean'
            ]);

            $schedule = ScheduleMaintenance::find($id);
            if(!$schedule){
                return response()->json(['message' => 'Schedule not found']);
            }

            $schedule->maintenance_request_id = $validatedData['maintenance_id'] ?? null;
            $schedule->schedule_title = $validatedData['title'];
            $schedule->status = $validatedData['status'];
            $schedule->text_color = $validatedData['text_color'];
            $schedule->bg_color = $validatedData['bg_color'];
            $schedule->start_date =  Carbon::createFromFormat('m/d/Y', $validatedData['start_date'])->format('Y-m-d');
            $schedule->end_date =  Carbon::createFromFormat('m/d/Y', $validatedData['end_date'])->format('Y-m-d');
            $schedule->unit_id = $validatedData['unitId'] ?? null;
            $schedule->unit_type = $validatedData['type'] ?? null;
            $schedule->maintenance_task =  $validatedData['maintenance_task'] ?? null;
            $schedule->description = $validatedData['description'] ?? null;
            $schedule->save();

            return response()->json([
                'message' => 'Schedule Maintenance Updated Successfully',
                'data' => $schedule
            ]);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Update Scheduled Maintenance', 'error' => $e->getMessage()], 500);
        }
    }

    public function Delete_Schedule($id)
    {
        try{
            $schedule = ScheduleMaintenance::find($id);
            if(!$schedule){
                return response()->json(['message' => 'Schedule not found']);
            }

            $schedule->delete();

            return response()->json([
                'message' => 'Schedule Maintenance Deleted Successfully',
                'data' => $schedule
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Delete Scheduled Maintenance', 'error' => $e->getMessage()], 500);
        }
    }
    

    public function Get_Maintenance_Status()
    {
        try{
            $statusMaintenance = ScheduleMaintenance::with('maintenanceRequest.tenant.rentalAgreement.rentedUnit', 'unit')->get();
            if(!$statusMaintenance){
                return response()->json(['message' => 'Schedule not found']);
            }
            return response()->json([
                'message' => 'Status Maintenance', 
                'data' => $statusMaintenance
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Query Maintenance Status', 'error' => $e->getMessage()], 500);
        }
    }

    public function Count_Status()
    {
        try{
            
                $todo = ScheduleMaintenance::where('status', 'To do')->count();
                $inprogress =  ScheduleMaintenance::where('status', 'In Progress')->count();
                $completed = ScheduleMaintenance::where('status', 'Completed')->count();
          

            return response()->json([
                'message' => 'Success status count',
                'todo' => $todo,
                'inprogress' => $inprogress,
                'completed' => $completed   
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Query Status', 'error' => $e->getMessage()], 500);
        }
    }


    public function Filter_Status($category)
    {
        try{
            $validStatuses = ['To do', 'Completed', 'In Progress'];

            if (!in_array($category, $validStatuses)) {
                return response()->json([
                    'message' => 'Invalid status',
                ], 400);
            }
            $results = ScheduleMaintenance::with('maintenanceRequest.tenant.rentalAgreement.rentedUnit')->where('status', $category)->get();

            if(!$results){
                return response()->json([
                    'message' => 'No Status found!'
                ]); 
            }

            return response()->json([
                'message' => 'Status found Successfully',
                'data' => $results
            ]);
           
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Query Status', 'error' => $e->getMessage()], 500);
        }
    }

    
    public function Filter_Maintenance_Request($category, $tenantId = null)
    {
        try{
            $validStatuses = ['Pending', 'Accepted', 'Rejected', 'Cancelled'];

            if (!in_array($category, $validStatuses)) {
                return response()->json([
                    'message' => 'Invalid status',
                ], 400);
            }

            $query = MaintenanceRequest::with('tenant.rentalAgreement.rentedUnit')->where('status', $category);

            if($tenantId){
                $query->where('tenant_id', $tenantId);
            }

            $scheduledMaintenance = $query->get();

            if(!$scheduledMaintenance){
                return response()->json(['message' => 'Schedule not found']);
            }

            return response()->json([
                'message' => 'Scheduled Maintenance', 
                'data' => $scheduledMaintenance
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Query Scheduled Maintenance', 'error' => $e->getMessage()], 500);
        }
    }

    //For UserDashboard Backend
    public function Requested_Maintenance_List($tenantId)
    {
        try{
            $requestMaintenance = MaintenanceRequest::where('tenant_id', $tenantId)->get();

            if(!$requestMaintenance){
                return response()->json(['message' => 'No Maintenance Request Found!'], 404);
            }

            return response()->json([
                'message' => 'Maintenance Request List', 
                'data' => $requestMaintenance
            ], 201);
            
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Retrieve Request Maintenance', 'error' => $e->getMessage()], 500);
        }
    }

    public function View_Accepted_Details($id)
    {
        try{
            $accepted = MaintenanceRequest::with('maintenanceImages')->where('id', $id)->first();
            if(!$accepted){
                return response()->json(['message' => 'Maintenance Request Not Found!'], 404);
            }

            return response()->json([
                'message' => 'Maintenance Request Details',
                'data' => $accepted
            ], 200);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Retrieve Accepted Maintenance Request', 'error' => $e->getMessage()], 500);
        }
    }

    public function Filter_Request($tenantId, $category)
    {
        try{
            $validStatuses = ['Pending', 'Accepted', 'Rejected'];

            if (!in_array($category, $validStatuses)) {
                return response()->json([
                    'message' => 'Invalid status',
                ], 400);
            }

            $scheduledMaintenance = MaintenanceRequest::with('images')->where('tenant_id', $tenantId)
            ->where('status', $category)
            ->get();

            if(!$scheduledMaintenance){
                return response()->json(['message' => 'Schedule not found']);
            }

            return response()->json([
                'message' => 'Scheduled Maintenance', 
                'data' => $scheduledMaintenance
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => 'Failed to Query Scheduled Maintenance', 'error' => $e->getMessage()], 500);
        }
    }















    
}
