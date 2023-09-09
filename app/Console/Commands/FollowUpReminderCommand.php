<?php

namespace App\Console\Commands;

use App\Models\FollowUpReminder;
use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Console\Command;

class FollowUpReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'follow-up:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $follow_ups = FollowUpReminder::whereDate('followup_session', now()->toDateString())->get();

        $message = 'You have a client follow up today.';

        foreach ($follow_ups as $item)
        {
            User::find($item->added_by)->notify(new AdminNotification($message, 'client-follow-up-reminder', $item->id));
        }
    }
}
