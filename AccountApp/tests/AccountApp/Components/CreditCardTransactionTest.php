<?php

declare(strict_types=1);

namespace AccountApp\tests\AccountApp\Components;

use AccountApp\Components\CreditCardTransaction;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CreditCardTransactionTest extends TestCase
{
    public function testConstructorPasses(): void
    {
        $config = [
            'amount' => 50,
            'payee' => 'Comcast',
        ];
        $transaction = new CreditCardTransaction($config);
        $data = $transaction->getData();
        self::assertEquals($config['amount'], $data['amount']);
    }

    public function testConstructorFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $config = [
            'amount' => 50,
            'payee' => 'Comcast',
            'banana' => 'Dole',
        ];
        (new CreditCardTransaction($config));
    }
}
