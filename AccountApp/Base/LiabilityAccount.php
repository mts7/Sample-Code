<?php

declare(strict_types=1);

namespace AccountApp\Base;

use AccountApp\Components\Helpers;
use AccountApp\Exceptions\OverTheLimitException;
use AccountApp\Interfaces\LiabilityAccountInterface;

class LiabilityAccount implements LiabilityAccountInterface
{
    /**
     * @var string Account ID.
     */
    protected string $accountId;

    /**
     * @var float Current amount owed.
     */
    protected float $balance = 0.00;

    /**
     * @var float Maximum amount allowable for borrowing.
     */
    protected float $limit = 0.00;

    /**
     * LiabilityAccount constructor.
     *
     * @param string $accountId
     */
    public function __construct(string $accountId)
    {
        $this->accountId = $accountId;
        // TODO: pull the balance from an API or a database table
    }

    /**
     * Increase the balance by the provided amount.
     *
     * @param float $amount Amount of debt incurred.
     *
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\OverTheLimitException
     */
    public function borrow(float $amount): void
    {
        Helpers::validatePositiveNumber($amount);

        if ($this->balance + $amount > $this->limit) {
            throw new OverTheLimitException($amount, $this->limit);
        }

        $this->balance += $amount;
    }

    /**
     * Decreases the balance by the provided amount.
     *
     * @param float $amount Amount of repayment.
     *
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     */
    public function repay(float $amount): void
    {
        Helpers::validatePositiveNumber($amount);

        $this->balance -= $amount;
    }

    /**
     * Gets the balance of the account.
     *
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * Gets the limit of the account.
     *
     * @return float
     */
    public function getLimit(): float
    {
        return $this->limit;
    }
}
