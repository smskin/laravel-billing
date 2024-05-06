# Billing Module for Laravel Projects
## Description
This module supports both synchronous and asynchronous interaction with billing.

## Concept
Any object can participate in billing. To do this, simply implement the Billingable interface and include the BillingableTrait. The billing system doesn't care if it's an object in the database or just a class with statically declared properties.

Applications:
- Billingable objects as users (transferring funds from one user to another)
- Billingable objects as accounts in the accounting system (e.g., user's own and credit accounts)
- Billingable objects as part of a business process (e.g., Customer -> Task -> Performer)

## Installation
```bash
composer require smskin/laravel-billing
php artisan vendor:publish --provider="SMSkin\Billing\Providers\ServiceProvider"
```

This will create:
- a configuration file at ```/config/billing.php```
- a database migration file at ```/database/migrations/<date>_create_billing_operations_table.php```

If necessary, you can change the table name for storing billing operations in the configuration file (```billing.table```).

```bash
php artisan migrate
```

Basic Usage

To start using billing, you need to create an object that implements the Billingable interface. For convenience, a Trait is prepared, which implements part of the interface methods - BillingableTrait.

```php
<?php

namespace App\Console\Commands;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Traits\BillingableTrait;

class User1 implements Billingable
{
    use BillingableTrait;

    /**
     * Object subsystem
     * @return string
     */
    function getBillingSubsystem(): string
    {
        return 'internal';
    }

    /**
     * Object type
     * @return string
     */
    function getBillingType(): string
    {
        return 'user';
    }

    /**
     * Object identity
     * @return string
     */
    function getBillingId(): string
    {
        return 1;
    }
}
```

Now you can create an instance of the User1 object and get its balance in the system:
```php
$user1 = new User1;
$balance = $user1->getBalance();
```

**Balance = sum of incoming operations - sum of outgoing operations**

## Module Features
- getBalances - bulk retrieval of balances for multiple entities (performed in a single database operation)
- increaseBalance - increase entity balance (synchronous)
- increaseBalanceAsync - increase entity balance (asynchronous)
- decreaseBalance - decrease entity balance (synchronous)
- decreaseBalanceAsync - decrease entity balance (asynchronous)
- transfer - transfer funds from one entity's balance to another entity's balance (synchronous)
- transferAsync - transfer funds from one entity's balance to another entity's balance (asynchronous)
- transferToMultipleRecipients - transfer funds from one entity's balance to multiple entities' balances (synchronous)
- transferToMultipleRecipientsAsync - transfer funds from one entity's balance to multiple entities' balances (asynchronous)
- createOperationsReport - report on billing operations

### getBalances
Sometimes it's necessary to retrieve the balances of multiple entities simultaneously.

```text
Example usage:
We have 2 accounts belonging to one user. For example, an account in the system and a card account. With this method, you can retrieve the balances of both objects with a single request.
```

```php
$account1 = new User1Own;
$account2 = new User1Card;
$balances = (new Billing)->getBalances(
    (new GetBalancesRequest)
        ->setAccounts(collect([$account1, $account2]))
);
```

```php
Illuminate\Support\Collection {#681
  #items: array:2 [
    0 => SMSkin\Billing\Models\Balance {#678
      #account: App\Console\Commands\User1Own {#661}
      #balance: 680.0
    }
    1 => SMSkin\Billing\Models\Balance {#674
      #account: App\Console\Commands\User1Card {#662}
      #balance: 150.0
    }
  ]
  #escapeWhenCastingToString: false
} 
```

### increaseBalance
This method is necessary to replenish the entity's balance from outside the system.

```php
$user1 = new User1Own();
(new Billing())->increaseBalance(
    (new BalanceOperationRequest())
        ->setOperationId(Str::uuid())
        ->setTarget($user1)
        ->setAmount(1000)
);
```

In synchronous execution, you may receive one of the Exceptions:
- NotUniqueOperationId - a non-unique operation ID was passed
- AmountMustBeMoreThan0 - the operation amount must be > 0

```php
$user1 = new User1Own();
(new Billing())->increaseBalanceAsync(
    (new BalanceOperationRequest())
        ->setOperationId(Str::uuid())
        ->setTarget($user1)
        ->setAmount(1000)
);
```
In asynchronous execution, one of the events will be sent to the EventBus:
- EBalanceIncreaseCompleted
- EBalanceIncreaseFailed

Both events will pass the input attributes, and the EBalanceIncreaseFailed event will pass an instance of Exception.

### decreaseBalance
This method is necessary to withdraw funds from the entity's balance in the system.

```php
$user1 = new User1Own();
(new Billing())->decreaseBalance(
    (new BalanceOperationRequest())
        ->setOperationId(Str::uuid())
        ->setTarget($user1)
        ->setAmount(1000)
);
```

In synchronous execution, you may receive one of the Exceptions:
- MutexException - error locking the sender entity of the payment (parallel operation)
- NotUniqueOperationId - a non-unique operation ID was passed
- AmountMustBeMoreThan0 - the operation amount must be > 0
- InsufficientBalance - insufficient funds on the balance

```php
$user1 = new User1Own();
(new Billing())->decreaseBalanceAsync(
    (new BalanceOperationRequest())
        ->setOperationId(Str::uuid())
        ->setTarget($user1)
        ->setAmount(1000)
);
```

In asynchronous execution, a restart is provided after 5 seconds when MutexException is received.

In asynchronous execution, one of the events will be sent to the EventBus:
- EDecreaseBalanceCompleted
- EDecreaseBalanceFailed

Both events will pass the input attributes, and the EDecreaseBalanceFailed event will pass an instance of Exception.

### transfer
This method is used to transfer funds from one entity's balance to another entity's balance.

```php
$user1 = new User1();
$user2 = new User2();
(new Billing())->transfer(
    (new TransferRequest())
        ->setOperationId(Str::uuid())
        ->setSender($user1)
        ->setRecipient($user2)
        ->setAmount(100)
);
```

In synchronous execution, you may receive one of the Exceptions:
- MutexException - error locking the sender entity of the payment (parallel operation)
- NotUniqueOperationId - a non-unique operation ID was passed
- AmountMustBeMoreThan0 - the operation amount must be > 0
- InsufficientBalance - insufficient funds on the balance
- RecipientIsSender - sender and recipient are the same entity

In asynchronous execution, a restart is provided after 5 seconds when MutexException is received.

In asynchronous execution, one of the events will be sent to the EventBus:
- ETransferCompleted
- ETransferFailed

Both events will pass the input attributes, and the ETransferFailed event will pass an instance of Exception.

### transferToMultipleRecipients
This method is used to transfer funds to multiple recipient entities.

```text
Example usage:
With a single transaction, you need to send a payment to the recipient + withhold a commission fee on the system's account. If there are not enough funds for both operations, an error should be issued.
```

```php
$user1 = new User1();
$user2 = new User2();
$user3 = new User3();
(new Billing())->transferToMultipleRecipients(
    (new TransferToMultipleRecipientsRequest())
        ->setSender($user1)
        ->setPayments(collect([
            (new Payment())
                ->setOperationId(\Str::uuid())
                ->setRecipient($user2)
                ->setAmount(50),
            (new Payment())
                ->setOperationId(Str::uuid())
                ->setRecipient($user3)
                ->setAmount(170)
            ]))
);
```

In synchronous execution, you may receive one of the Exceptions:
- MutexException - error locking the sender entity of the payment (parallel operation)
- NotUniqueOperationId - a non-unique operation ID was passed
- AmountMustBeMoreThan0 - the operation amount must be > 0
- InsufficientBalance - insufficient funds on the balance
- RecipientIsSender - sender and recipient are the same entity

In asynchronous execution, a restart is provided after 5 seconds when MutexException is received.

In asynchronous execution, one of the events will be sent to the EventBus:
- EBulkTransferCompleted
- EBulkTransferFailed

Both events will pass the input attributes, and the EBulkTransferFailed event will pass an instance of Exception.

### createOperationsReport
Method for generating a balance report. **Under development**.

```php
$report = (new Billing)->createOperationsReport(
    (new CreateOperationsReportRequest)
        ->setPerPage(25)
        ->setPage(1)
);
```