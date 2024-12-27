<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Account;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewNotifications;


class SendNewNotifications
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    // public function handle(object $event): void
    // {
    //     //
    // }*/

    public function handle($event): void
    {
        $maintenance = $event->maintenance;

        // Notify all admins
        $admins = Account::where('user_type', 'Landlord')->get();
        foreach ($admins as $admin) {
            $admin->notify(new MaintenanceRequestNotification($maintenance, 'Landlord'));
        }

         // Notify the tenant
         $tenant = Account::find($maintenance->tenant_id); // Assuming `tenant_id` exists in the maintenance record
         if ($tenant) {
             $tenant->notify(new NewNotifications($maintenance, 'Tenant'));
         }
    }
    // public function handle($event)
    // {
    //     $landlord = Account::where('user_type', 'Landlord')->get();
    //     Notification::send($landlord, new NewNotifications($event->user));
    // }
}
