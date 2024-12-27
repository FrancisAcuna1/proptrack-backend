<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\MaintenanceRequest;
use App\Models\Account;

class NewNotifications extends Notification
{
    use Queueable;
    private $maintenanceRequest;
    private $unitName;
    private $role;
    /**
     * Create a new notification instance.
     */
    public function __construct(MaintenanceRequest $maintenanceRequest, $unitName, $role)
    {
        $this->maintenanceRequest = $maintenanceRequest;
        $this->unitName = $unitName;
        $this->role = $role;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    // public function via(object $notifiable): array
    // {
    //     return ['mail'];
    // }
    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $tenant = Account::find($this->maintenanceRequest->tenant_id);

        if($this->maintenanceRequest->status === 'Accepted'){
            return [
                'maintenance_id' => $this->maintenanceRequest->id,
                'reciever_firstname' => $tenant->firstname, 
                'reciever_lastname' => $tenant->lastname,
                'sender' => 'Landlord',
                'message' => "Your maintenance request for {$this->maintenanceRequest->item_name} has been accepted.",
                'reported_issue' => $this->maintenanceRequest->item_name,
                'role' => $this->role, 
            ];
        }
        else if($this->maintenanceRequest->status === 'Rejected'){
            return [
                'maintenance_id' => $this->maintenanceRequest->id,
                'reciever_firstname' => $tenant->firstname, 
                'reciever_lastname' => $tenant->lastname,
                'sender' => 'Landlord',
                'message' => "Your maintenance request for {$this->maintenanceRequest->item_name} has been rejected.",
                'reported_issue' => $this->maintenanceRequest->item_name,
                'role' => $this->role, 
            ];

        }else if($this->maintenanceRequest->status === 'Cancelled'){
            return [
                'maintenance_id' => $this->maintenanceRequest->id,
                'sender_firstname' => $tenant->firstname, 
                'sender_lastname' => $tenant->lastname,
                'message' => "Maintenance Request has been cancelled by {$tenant->firstname}{$tenant->lastname}.",
                'reported_issue' => $this->maintenanceRequest->item_name,
                'role' => $this->role, 
            ];
        }
        else{

            $message = $this->role === 'Landlord' 
            ? "New maintenance request reported for {$this->unitName} "
            : "Your maintenance request for {$this->maintenanceRequest->item_name} has been submitted";
            return [
                'maintenance_id' => $this->maintenanceRequest->id,
                'sender_firstname' => $tenant->firstname, 
                'sender_lastname' => $tenant->lastname,
                'message' => $message,
                'reported_issue' => $this->maintenanceRequest->item_name,
                'role' => $this->role,
            ];
        }
       
    }



    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            
        ];
    }
    
}
