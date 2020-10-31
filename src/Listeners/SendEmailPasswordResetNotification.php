<?php

namespace Mupi\AuthPac\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\CanResetPassword;

class SendEmailPasswordResetNotification
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\PasswordReset  $event
     * @return void
     */
    public function handle(PasswordReset $event)
    {
        if ($event->user instanceof CanResetPassword ) {
            $event->user->customSendEmailRecoverPassNotification();
        }
    }
}
