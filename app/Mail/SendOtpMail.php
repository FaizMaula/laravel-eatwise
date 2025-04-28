<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp, $fullname, $username;

    public function __construct($otp, $fullname, $username)
    {
        $this->otp = $otp;
        $this->fullname = $fullname;
        $this->username = $username;
    }

    public function build()
    {
        return $this->subject('OTP Verification')
                    ->html('
                        <h2>Hello ' . $this->fullname . ' (' . $this->username . ')!</h2>
                        <p>Thank you for registering at EatWise.</p>
                        <p>Your OTP code is:</p>
                        <h1>' . $this->otp . '</h1>
                        <p>Please use this code to verify your email. This code is valid for 10 minutes.</p>
                    ');
    }
}

