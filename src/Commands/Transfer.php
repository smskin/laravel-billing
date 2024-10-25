<?php

namespace SMSkin\Billing\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use SMSkin\Billing\Billing;
use SMSkin\Billing\Exceptions\AmountMustBeMoreThan0;
use SMSkin\Billing\Exceptions\InsufficientBalance;
use SMSkin\Billing\Exceptions\NotUniqueOperationId;
use SMSkin\Billing\Exceptions\RecipientIsSender;
use SMSkin\Billing\Models\BillingSubject;
use SMSkin\LaravelSupport\Exceptions\MutexException;

class Transfer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules.billing:transfer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer';

    public function handle()
    {
        $operationId = $this->ask('Operation id', Str::uuid()->toString());
        $sender = (new BillingSubject())
            ->setSubsystem($this->ask('Sender: Billing subsystem', 'local'))
            ->setType($this->ask('Sender: Billing type', 'user'))
            ->setId($this->ask('Sender: Billing id'));

        $this->info('Balance of sender: ' . $sender->getBalance());

        $recipient = (new BillingSubject())
            ->setSubsystem($this->ask('Recipient: Billing subsystem', 'local'))
            ->setType($this->ask('Recipient: Billing type', 'user'))
            ->setId($this->ask('Recipient: Billing id'));

        $this->info('Balance of recipient: ' . $recipient->getBalance());

        try {
            (new Billing())->transfer(
                $operationId,
                $sender,
                $recipient,
                $this->ask('Amount')
            );
        } catch (RecipientIsSender) {
            $this->error('The recipient is the sender of the payment');
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
