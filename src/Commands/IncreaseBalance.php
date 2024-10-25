<?php

namespace SMSkin\Billing\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use SMSkin\Billing\Billing;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Models\BillingSubject;

class IncreaseBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules.billing:increase-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Increase balance of subject';

    /**
     */
    public function handle()
    {
        $operationId = $this->ask('Operation id', Str::uuid()->toString());
        $subject = (new BillingSubject())
            ->setSubsystem($this->ask('Billing subsystem', 'local'))
            ->setType($this->ask('Billing type', 'user'))
            ->setId($this->ask('Billing id'));

        try {
            (new Billing())->increaseBalance(
                $operationId,
                $subject,
                $this->ask('Amount')
            );
        } catch (AmountMustBeMoreThan0) {
            $this->error('Amount must be more than 0');
        } catch (NotUniqueOperationId $exception) {
            $this->error('Not unique operation id (' . $exception->getOperationId() . ')');
        }
    }
}
