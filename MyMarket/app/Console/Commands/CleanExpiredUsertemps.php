<?php

namespace App\Console\Commands;

use App\Models\Usertemp;
use Illuminate\Console\Command;

class CleanExpiredUsertemps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-expired-usertemps';

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
        $count = Usertemp::where("expire_at", "<", now())->delete();
        $this->info("წაიშალა {$count} ვადაგასული usertemp ჩანაწერი.");
    }
}
