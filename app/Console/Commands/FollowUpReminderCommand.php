<?php

namespace App\Console\Commands;

use App\Models\FollowUpReminder;
use App\Models\User;
use App\Notifications\AdminNotification;
use App\Notifications\FollowUpReminderNotification;
use Carbon\Carbon;
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

        foreach ($follow_ups as $item)
        {
            $message = 'You have a client follow up today at '. Carbon::parse($item->followup_session)->format('H:i') .'.';

            $user = User::find($item->added_by);

            $user->notify(new AdminNotification($message, 'client-follow-up-reminder', $item->id));

            $user->notify(new FollowUpReminderNotification($item->client, $item->followup_session, $user->name));
        }
    }
}
