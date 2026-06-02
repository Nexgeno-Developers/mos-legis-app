<?php

// app/Mail/FormSubmissionMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

//class FormSubmissionMail extends Mailable implements ShouldQueue
class FormSubmissionMail extends Mailable
{
    //use Queueable, SerializesModels;
    use SerializesModels;

    public $formName;
    public $data;

    public function __construct($formName, $data)
    {
        $this->formName = $formName;
        $this->data = $data;
    }

    public function build()
    {
        // return $this->subject(config('custom.app_name'))
        //             ->markdown('emails.form_submission');
        return $this->from($this->data['email'], $this->data['name'] ?? null) // set user email as from
               ->subject(config('custom.app_name'))
               ->markdown('emails.form_submission');        
    }
}