<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Event;

class sendEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:event';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Events to Subscribers';

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
        $event = new Event();
        $event->eventApp();
        $this->info('Success :)');
    }
}
