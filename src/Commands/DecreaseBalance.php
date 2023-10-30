<?php

namespace SMSkin\Billing\Commands;

use Illuminate\Support\Str;
use SMSkin\Billing\Billing;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Models\BillingSubject;
use SMSkin\Billing\Requests\BalanceOperationRequest;
use Illuminate\Console\Command;
use SMSkin\LaravelSupport\Exceptions\MutexException;

class DecreaseBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules.billing:decrease-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decrease balance of subject';

    public function handle()
    {
        $operationId = $this->ask('Operation id', Str::uuid()->toString());
        $subject = (new BillingSubject)
            ->setSubsystem($this->ask('Billing subsystem', 'local'))
            ->setType($this->ask('Billing type', 'user'))
            ->setId($this->ask('Billing id'));

        $this->info('Balance of subject: ' . $subject->getBalance());

        try {
            (new Billing())->decreaseBalance(
                (new BalanceOperationRequest())
                    ->setOperationId($operationId)
                    ->setTarget($subject)
                    ->setAmount($this->ask('Amount'))
            );
        } catch (InsufficientBalance $exception) {
            $this->error('Insufficient funds in the account (Current balance: ' . $exception->getBalance() . ', amount: ' . $exception->getAmount() . ')');
        } catch (AmountMustBeMoreThan0) {
            $this->error('Amount must be more than 0');
        } catch (NotUniqueOperationId $exception) {
            $this->error('Not unique operation id (' . $exception->getOperationId() . ')');
        } catch (MutexException) {
            $this->error('Sender account is locked for another operation');
        }
    }
}
