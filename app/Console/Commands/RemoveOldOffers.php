<?php

namespace App\Console\Commands;

use App\Offer;
use Illuminate\Console\Command;

/**
 * Console command that removes offers that are over a year old
 */
class RemoveOldOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offer:remove-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove offers that are over a year old';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Offer::toBeRemoved()->delete();

        $this->info("$count offers were removed.");
    }
}
