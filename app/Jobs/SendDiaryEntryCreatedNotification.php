<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\DiaryEntryCreatedMail;
use App\Models\DiaryEntry;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendDiaryEntryCreatedNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DiaryEntry $entry,
        public User $user,
    ) {
    }

    public function handle(): void
    {
        Mail::to($this->user->email)->send(new DiaryEntryCreatedMail($this->entry));
    }
}
