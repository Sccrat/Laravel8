<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Common\PickingConfig;

class LogTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Esto va a tirar un log papu';

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
     *
     * @return mixed
     */
    public function handle()
    {
        \Log::info('papurris a las ' . \Carbon\Carbon::now());
        //PickingConfig::CheckStockPickingConfig();
    }
}
