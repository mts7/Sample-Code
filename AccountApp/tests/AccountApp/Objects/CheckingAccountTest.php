<?php

declare(strict_types=1);

namespace AccountApp\tests\AccountApp\Objects;

use AccountApp\Exceptions\InvalidTransferAccountException;
use AccountApp\Exceptions\NegativeNotAllowedException;
use AccountApp\Exceptions\NonSufficientFundsException;
use AccountApp\Objects\CheckingAccount;
use ArgumentCountError;
use PHPUnit\Framework\TestCase;
use TypeError;

class CheckingAccountTest extends TestCase
{
    /**
     * @var string Mock account ID.
     */
    protected string $accountId = 'checking-account-test';

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
        $account = new CheckingAccount($this->accountId);
        self::assertEquals(CheckingAccount::class, get_class($account));
    }

    /**
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     */
    public function testCanDeposit(): void
    {
        $amount = 50;
        $account = new CheckingAccount($this->accountId);
        $account->deposit($amount);
        $balance = $account->getBalance();
        self::assertEquals($amount, $balance, 'Could not verify matching balance.');
    }

    /**
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\NonSufficientFundsException
     */
    public function testCanWithdrawFromFunds(): void
    {
        $deposit = 50;
        $withdraw = 25;
        $account = new CheckingAccount($this->accountId);
        $account->deposit($deposit);
        $account->withdraw($withdraw);
        $balance = $deposit - $withdraw;
        self::assertEquals($balance, $account->getBalance(), 'The new balance is off.');
    }

    /**
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\NonSufficientFundsException
     */
    public function testGetExceptionWhenWithdrawFunds(): void
    {
        $amount = 50;
        $account = new CheckingAccount($this->accountId);
        $this->expectException(NonSufficientFundsException::class);
        $account->withdraw($amount);
    }

    /**
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\NonSufficientFundsException
     */
    public function testWriteChecks(): void
    {
        $initialBalance = 1000;
        $account = new CheckingAccount($this->accountId);
        $account->deposit($initialBalance);
        $expectedBalance = $initialBalance;
        foreach ($this->transactions as $array) {
            $account->writeCheck($array['amount'], $array['payee']);
            $expectedBalance -= $array['amount'];
        }
        $balance = $account->getBalance();
        self::assertEquals($expectedBalance, $balance, 'The balance is off after writing checks.');
    }

    /**
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\NonSufficientFundsException
     */
    public function testGetTransactions(): void
    {
        $initialBalance = 1000;
        $account = new CheckingAccount($this->accountId);
        $account->deposit($initialBalance);
        $expectedTotal = 0;
        foreach ($this->transactions as $array) {
            $account->writeCheck($array['amount'], $array['payee']);
            $expectedTotal += $array['amount'];
        }
        $transactions = $account->getTransactions();
        $actualTotal = 0;
        foreach ($transactions as $checkRegistry) {
            $transaction = $checkRegistry->getData();
            $actualTotal += $transaction['amount'];
        }
        self::assertEquals($expectedTotal, $actualTotal, 'The totals from the transactions do not match.');
    }

    /**
     * Checks for no account specified.
     */
    public function testCannotLoadWithoutAccount(): void
    {
        $this->expectException(ArgumentCountError::class);
        (new CheckingAccount());
    }

    public function testInvalidAccountId(): void
    {
        $this->expectException(TypeError::class);
        (new CheckingAccount(123));
    }

    /**
     * @throws \AccountApp\Exceptions\InvalidTransferAccountException
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\NonSufficientFundsException
     */
    public function testTransferWithInvalidAccounts(): void
    {
        $this->expectException(InvalidTransferAccountException::class);
        $amount = 1000;
        $account = new CheckingAccount($this->accountId);
        $account->deposit($amount);
        $account->transfer('banana', 'apple', 50);
    }

    /**
     * @throws \AccountApp\Exceptions\InvalidTransferAccountException
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\NonSufficientFundsException
     */
    public function testTransferToAccount(): void
    {
        $amount = 1000;
        $transaction = [
            'account' => 'banana',
            'amount' => 50,
        ];
        $account = new CheckingAccount($this->accountId);
        $account->deposit($amount);
        $account->transfer($this->accountId, $transaction['account'], $transaction['amount']);
        $balance = $account->getBalance();
        self::assertEquals(
            $amount - $transaction['amount'], $balance, 'The transfer failed to withdraw the correct amount.'
        );
    }

    /**
     * @throws \AccountApp\Exceptions\InvalidTransferAccountException
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\NonSufficientFundsException
     */
    public function testTransferFromAccount(): void
    {
        $this->expectException(NonSufficientFundsException::class);
        $amount = 1000;
        $transaction = [
            'account' => 'banana',
            'amount' => 50,
        ];
        $account = new CheckingAccount($this->accountId);
        $account->deposit($amount);
        $account->transfer($transaction['account'], $this->accountId, $transaction['amount']);
        // the below code will be good to use once there is a way to pull account data from somewhere
//        $balance = $account->getBalance();
//        self::assertEquals(
//            $amount + $transaction['amount'], $balance, 'The transfer failed to withdraw the correct amount.'
//        );
    }

    /**
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     */
    public function testNegativeAmount(): void
    {
        $this->expectException(NegativeNotAllowedException::class);
        $amount = -50;
        $account = new CheckingAccount($this->accountId);
        $account->deposit($amount);
    }
}
