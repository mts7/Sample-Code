<?php

declare(strict_types=1);

namespace AccountApp\tests\AccountApp\Objects;

use AccountApp\Exceptions\NegativeNotAllowedException;
use AccountApp\Exceptions\OverTheLimitException;
use AccountApp\Objects\CreditCardAccount;
use ArgumentCountError;
use PHPUnit\Framework\TestCase;
use TypeError;

class CreditCardAccountTest extends TestCase
{
    /**
     * @var string Mock account ID.
     */
    protected string $accountId = 'credit-card-account-test';

    /**
     * @var array|array[] Transactions for testing.
     */
    protected array $transactions = [
        [
            'amount' => 100.00,
            'payee' => 'Spectrum',
        ],
        [
            'amount' => 150.00,
            'payee' => 'Publix',
        ],
        [
            'amount' => 350.00,
            'payee' => 'Toyota',
        ],
    ];

    public function testCanCreateWithAccount(): void
    {
        $account = new CreditCardAccount($this->accountId);
        self::assertEquals(CreditCardAccount::class, get_class($account));
    }

    /**
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\OverTheLimitException
     */
    public function testPay(): void
    {
        $limit = 1000;
        $account = new CreditCardAccount($this->accountId);
        $account->setLimit($limit);
        $expectedBalance = 0;
        foreach ($this->transactions as $transaction) {
            $account->pay($transaction['amount'], $transaction['payee']);
            $expectedBalance += $transaction['amount'];
        }
        self::assertEquals($expectedBalance, $account->getBalance(), 'The balance is off after paying.');
    }

    /**
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\OverTheLimitException
     */
    public function testGetTransactions(): void
    {
        $limit = 1000;
        $account = new CreditCardAccount($this->accountId);
        $account->setLimit($limit);
        $paid = 0;
        foreach ($this->transactions as $transaction) {
            $account->pay($transaction['amount'], $transaction['payee']);
            $paid += $transaction['amount'];
        }
        $transactions = $account->getTransactions();
        $actualPaid = 0;
        foreach ($transactions as $transaction) {
            $data = $transaction->getData();
            $actualPaid += $data['amount'];
        }
        self::assertEquals($paid, $actualPaid, 'The transaction totals do not match.');
    }

    /**
     * Checks for no account specified.
     */
    public function testCannotLoadWithoutAccount(): void
    {
        $this->expectException(ArgumentCountError::class);
        (new CreditCardAccount());
    }

    public function testInvalidAccountId(): void
    {
        $this->expectException(TypeError::class);
        (new CreditCardAccount(123));
    }

    /**
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\OverTheLimitException
     */
    public function testCreditOfAccount(): void
    {
        $creditTransaction = [
            'amount' => 50,
            'payer' => 'Spectrum',
        ];
        $limit = 1000;
        $account = new CreditCardAccount($this->accountId);
        $account->setLimit($limit);
        $paid = 0;
        foreach ($this->transactions as $transaction) {
            $account->pay($transaction['amount'], $transaction['payee']);
            $paid += $transaction['amount'];
        }
        $account->credit($creditTransaction['amount'], $creditTransaction['payer']);
        $paid -= $creditTransaction['amount'];
        $transactions = $account->getTransactions();
        $actualPaid = 0;
        foreach ($transactions as $transaction) {
            $data = $transaction->getData();
            $actualPaid += $data['amount'];
        }
        self::assertEquals($paid, $actualPaid, 'The transaction totals do not match.');
    }

    public function testGetLimit(): void
    {
        $limit = 1000;
        $account = new CreditCardAccount($this->accountId);
        $account->setLimit($limit);
        self::assertEquals($limit, $account->getLimit(), 'The limits do not match.');
    }

    /**
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\OverTheLimitException
     */
    public function testExceedLimit(): void
    {
        $this->expectException(OverTheLimitException::class);
        $limit = 1000;
        $account = new CreditCardAccount($this->accountId);
        $account->setLimit($limit);
        $account->pay($limit * 2, 'banana');
    }

    /**
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     */
    public function testNegativeAmount(): void
    {
        $this->expectException(NegativeNotAllowedException::class);
        $amount = -50;
        $limit = 1000;
        $account = new CreditCardAccount($this->accountId);
        $account->setLimit($limit);
        $account->credit($amount, 'banana');
    }
}
