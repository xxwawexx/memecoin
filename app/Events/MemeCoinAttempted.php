<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemeCoinAttempted
{
    use Dispatchable, SerializesModels;

    public $user, $fullName, $attemptedName, $attemptNumber, $status, $error;

    public function __construct($user, $fullName, $attemptedName, $attemptNumber, $status, $error = null)
    {
        $this->user          = $user;
        $this->fullName      = $fullName;
        $this->attemptedName = $attemptedName;
        $this->attemptNumber = $attemptNumber;
        $this->status        = $status;
        $this->error         = $error;
    }
}
