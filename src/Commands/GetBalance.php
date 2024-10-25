<?php

namespace SMSkin\Billing\Commands;

use Illuminate\Console\Command;
use SMSkin\Billing\Models\BillingSubject;

class GetBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules.billing:get-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Increase balance';

    public function handle()
    {
        $subject = (new BillingSubject())
            ->setSubsystem($this->ask('Billing subsystem', 'local'))
            ->setType($this->ask('Billing type', 'user'))
            ->setId($this->ask('Billing id'));

        $this->info('Balance of subject: ' . $subject->getBalance());
    }
}
