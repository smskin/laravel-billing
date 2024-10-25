# Модуль биллинга для Laravel проектов
## Описание
Данный модуль поддерживает как синхронный, так и асинхронный способ взаимодействия с биллигом.
## Концепция
Любой объект может быть участником биллинга. Для этого достаточно реализовать интерфейс Billingable и подключить BillingableTrait.
Биллингу все равно что это будет - объект в БД или просто класс со статично объявленными свойствами.

Применение:
- объекты Billingable как пользователи (перевод средств от пользователя пользователю)
- объекты Billingable как счета в учетной системе (например own и credit счета пользователя)
- объекты Billingable как объекты некоего бизнес процесса (например Заказчик -> Задача -> Исполнитель)

## Установка
```bash
composer require smskin/laravel-billing
php artisan vendor:publish --provider="SMSkin\Billing\Providers\ServiceProvider"
```
В результате появятся 
- файл конфигурации ``/config/billing.php``
- файл миграции БД ``/database/migrations/<date>_create_billing_operations_table.php``

В случае необходимости вы можете изменить название таблицы для хранения биллинговых операций в файле конфигурации (``billing.table``)
```bash
php artisan migrate
```

## Базовое использование
Для начала использования биллинга необходимо создать объект, реализующий интерфейс Billingable. 
Для удобства работы подготовлен Trait, реализующий часть методов интерфейса - BillingableTrait.

```php
<?php

namespace App\Console\Commands;

use SMSkin\Billing\Contracts\Billingable;
use SMSkin\Billing\Traits\BillingableTrait;

class User1 implements Billingable
{
    use BillingableTrait;

    /**
     * Подсистема сущности
     * @return string
     */
    function getBillingSubsystem(): string
    {
        return 'internal';
    }

    /**
     * Тип сущности
     * @return string
     */
    function getBillingType(): string
    {
        return 'user';
    }

    /**
     * Идентификатор сущности
     * @return string
     */
    function getBillingId(): string
    {
        return 1;
    }
}
```

Теперь вы можете создать экземпляр объекта User1 и получить его баланс в системе:
```php
$user1 = new User1;
$balance = $user1->getBalance();
```

``Баланс = сумма входящих операций - сумма исходящих операций``

## Возможности модуля
- getBalances - массовое получение балансов нескольких сущностей (выполняется одной операцией БД)
- increaseBalance - увеличение баланса сущности (синхронное)
- increaseBalanceAsync - увеличение баланса сущности (асинхронное)
- decreaseBalance - уменьшение баланса сущности (синхронное)
- decreaseBalanceAsync - уменьшение баланса сущности (асинхронное)
- transfer - перевод средств с баланса одной сущности на баланс другой сущности (синхронное)
- transferAsync - перевод средств с баланса одной сущности на баланс другой сущности (асинхронное)
- transferToMultipleRecipients - перевод средств с баланса одной сущности на баланс нескольких сущностей (синхронное)
- transferToMultipleRecipientsAsync - перевод средств с баланса одной сущности на баланс нескольких сущностей (асинхронное)
- createOperationsReport - отчет по операциям биллинга

### getBalances
Иногда необходимо получить баланс нескольких сущностей одновременно. 
```text
Пример использования: 
У нас есть 2 счета, относящиеся к одному пользователю. 
Например счет в системе и счет на карте. 
С помощью данного метода можно одним запросом получить балансы обоих объектов.
```

```php
$account1 = new User1Own;
$account2 = new User1Card;
$balances = (new Billing)->getBalances(collect([$account1, $account2]));
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
Данный метод необходим для пополение баланса сущности из-вне системы

```php
$user1 = new User1Own();
(new Billing())->increaseBalance(Str::uuid(), $user1, 100);
```

При синхронном исполнении можно получить один из Exception:
- NotUniqueOperationId - передан не уникальный ID операции
- AmountMustBeMoreThan0 - сумма операции должна быть > 0

```php
$user1 = new User1Own();
(new Billing())->increaseBalanceAsync(Str::uuid(), $user1, 100);
```

При асинхронном исполнении в EventBus будет отправлен один из эвентов:
- EBalanceIncreaseCompleted
- EBalanceIncreaseFailed

В обоих эвентах будут переданны входящие атрибуты + в эвенте EBalanceIncreaseFailed будет передан экзмпляр Exception

### decreaseBalance
Данный метод необходим для вывода средств с баланса сущности системы

```php
$user1 = new User1Own();
(new Billing())->decreaseBalance(Str::uuid(), $user1, 100);
```

При синхронном исполнении можно получить один из Exception:
- MutexException - ошибка блокировки сущности отправителя платежа (паралельная операция)
- NotUniqueOperationId - передан не уникальный ID операции
- AmountMustBeMoreThan0 - сумма операции должна быть > 0
- InsufficientBalance - недостаточно средств на балансе

```php
$user1 = new User1Own();
(new Billing())->decreaseBalanceAsync(Str::uuid(), $user1, 100);
```

При асинхронном исполнении предусмотрен рестарт через 5 секунд при получении MutexException.

При асинхронном исполнении в EventBus будет отправлен один из эвентов:
- EDecreaseBalanceCompleted
- EDecreaseBalanceFailed

В обоих эвентах будут переданны входящие атрибуты + в эвенте EDecreaseBalanceFailed будет передан экзмпляр Exception

### transfer
Данный метод необходим для перевода средств с баланса одной сущности на баланс другой сущности

```php
$user1 = new User1();
$user2 = new User2();
(new Billing())->transfer(Str::uuid(), $user1, $user2, 100);
```

При синхронном исполнении можно получить один из Exception:
- MutexException - ошибка блокировки сущности отправителя платежа (паралельная операция)
- NotUniqueOperationId - передан не уникальный ID операции
- AmountMustBeMoreThan0 - сумма операции должна быть > 0
- InsufficientBalance - недостаточно средств на балансе
- RecipientIsSender - отправитель и получатель одно и та же сущность

При асинхронном исполнении предусмотрен рестарт через 5 секунд при получении MutexException.

При асинхронном исполнении в EventBus будет отправлен один из эвентов:
- ETransferCompleted
- ETransferFailed

В обоих эвентах будут переданны входящие атрибуты + в эвенте ETransferFailed будет передан экзмпляр Exception

### transferToMultipleRecipients
Данный метод необходим для одновременного перевода на несколько сущностей получателей

```text
Пример использования:
Необходимо одной транзакцией отправить платеж получателю + удержать на счет комиссии системы.
Если средств не хватит на обе операции - выдать ошибку
```

```php
$user1 = new User1();
$user2 = new User2();
$user3 = new User3();
(new Billing())
            ->transferToMultipleRecipients(
                $user1,
                collect([
                    (new Payment())
                        ->setOperationId(\Str::uuid())
                        ->setRecipient($user2)
                        ->setAmount(50),
                    (new Payment())
                        ->setOperationId(Str::uuid())
                        ->setRecipient($user3)
                        ->setAmount(170)
                ])
            );
```

При синхронном исполнении можно получить один из Exception:
- MutexException - ошибка блокировки сущности отправителя платежа (паралельная операция)
- NotUniqueOperationId - передан не уникальный ID операции
- AmountMustBeMoreThan0 - сумма операции должна быть > 0
- InsufficientBalance - недостаточно средств на балансе
- RecipientIsSender - отправитель и получатель одно и та же сущность

При асинхронном исполнении предусмотрен рестарт через 5 секунд при получении MutexException.

При асинхронном исполнении в EventBus будет отправлен один из эвентов:
- EBulkTransferCompleted
- EBulkTransferFailed

В обоих эвентах будут переданны входящие атрибуты + в эвенте EBulkTransferFailed будет передан экзмпляр Exception

### createOperationsReport
Метод генерации балансового отчета. В разработке.

```php
$report = (new Billing)->createOperationsReport(1, 25);
```