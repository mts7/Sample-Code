<?php

declare(strict_types=1);

namespace AccountApp\Objects;

use AccountApp\Base\LiabilityAccount;
use AccountApp\Components\CreditCardTransaction;

class CreditCardAccount extends LiabilityAccount
{
    /**
     * @var CreditCardTransaction[]
     */
    protected array $transactions = [];

    /**
     * Pay for something from this account and save the transaction.
     *
     * @param float $amount Amount paid.
     * @param string $payee Receiver of funds.
     * @param string|null $category Category of purchase.
     * @param string|null $date Date of transaction.
     *
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\OverTheLimitException
     */
    public function pay(float $amount, string $payee, string $category = null, string $date = null): void
    {
        $this->borrow($amount);
        $transaction = new CreditCardTransaction(
            [
                'amount' => $amount,
                'payee' => $payee,
                'category' => $category,
                'date' => $date,
            ]
        );
        $this->transactions[] = $transaction;
    }

    /**
     * Credit the account with the provided amount and save the transaction.
     *
     * @param float $amount Amount of credit (repayment).
     * @param string $payer Subject providing the credit.
     * @param string|null $category Category of the credit.
     * @param string|null $date Date of the credit.
     *
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     */
    public function credit(float $amount, string $payer, string $category = null, string $date = null): void
    {
        $this->repay($amount);
        $transaction = new CreditCardTransaction(
            [
                'amount' => $amount * -1,
                'payee' => $payer,
                'category' => $category,
                'date' => $date,
            ]
        );
        $this->transactions[] = $transaction;
    }

    /**
     * Get the transactions for the account.
     *
     * @return \AccountApp\Components\CreditCardTransaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    /**
     * Sets the limit.
     *
     * @param float $limit
     *
     * @todo Pull the limit from an API or a database table and change this to protected.
     */
    public function setLimit(float $limit): void
    {
        $this->limit = $limit;
    }
}
