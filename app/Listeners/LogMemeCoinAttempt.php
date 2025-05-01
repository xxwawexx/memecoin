<?php

namespace App\Listeners;

use App\Events\MemeCoinAttempted;
use App\Models\MemeCoinAttempt;

class LogMemeCoinAttempt
{
    public function handle(MemeCoinAttempted $event)
    {
        MemeCoinAttempt::create([
            'user_id'        => $event->user->id,
            'full_name'      => $event->fullName,
            'attempted_name' => $event->attemptedName,
            'attempt_number' => $event->attemptNumber,
            'status'         => $event->status,
            'error_message'  => $event->error
        ]);
    }
}
