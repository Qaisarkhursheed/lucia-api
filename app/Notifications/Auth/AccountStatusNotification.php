<?php

namespace App\Notifications\Auth;

use App\Events\UserStatusChangedEvent;
use App\Mail\Auth\AccountApprovedMail;
use App\Mail\Auth\AccountPendingMail;
use App\Mail\Auth\AccountRejectedMail;
use App\ModelsExtended\AccountStatus;
use App\ModelsExtended\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class AccountStatusNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     * @return AccountApprovedMail|AccountPendingMail|AccountRejectedMail|\App\Mail\Copilot\AccountApprovedMail
     * @throws \Exception
     */
    public function toMail($notifiable)
    {
//        Log::info("checking .. ", [$notifiable->roles->toArray(),  $notifiable->lastestUserRole()->toArray() ]);
        if( $notifiable->account_status_id === AccountStatus::PENDING_APPROVAL )
            return new AccountPendingMail( $notifiable );

        if( $notifiable->account_status_id === AccountStatus::APPROVED )
            return $notifiable->lastestUserRole()->isCopilot() ? new \App\Mail\Copilot\AccountApprovedMail($notifiable) : new AccountApprovedMail( $notifiable );

        if( $notifiable->account_status_id === AccountStatus::REJECTED )
            return new AccountRejectedMail( $notifiable );

        throw new \Exception( "Unknown Account Status Detected!" );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    /**
     * Handle the event.
     *
     * @param UserStatusChangedEvent $event
     * @return void
     */
    public function handle(UserStatusChangedEvent $event)
    {
        $event->user->notify( $this );
    }
}
