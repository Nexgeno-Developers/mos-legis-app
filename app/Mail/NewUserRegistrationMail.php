<?php

// app/Mail/NewUserRegistrationMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

//class NewUserRegistrationMail extends Mailable implements ShouldQueue
class NewUserRegistrationMail extends Mailable
{
    //use Queueable, SerializesModels;
    use SerializesModels;

    public $user;
    public $registrationMethod;

    public function __construct($user, $registrationMethod)
    {
        $this->user = $user;
        $this->registrationMethod = $registrationMethod;
    }

    public function build()
    {
        return $this->subject('New User Registration on ' . config('custom.app_name'))
                   ->markdown('emails.new_user_registration');        
    }
}