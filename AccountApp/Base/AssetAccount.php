<?php

declare(strict_types=1);

namespace AccountApp\Base;

use AccountApp\Components\Helpers;
use AccountApp\Exceptions\NonSufficientFundsException;
use AccountApp\Exceptions\InvalidTransferAccountException;
use AccountApp\Interfaces\AssetAccountInterface;

/**
 * Class AssetAccount
 *
 * @package AccountApp
 */
class AssetAccount implements AssetAccountInterface
{
    /**
     * @var string Bank Account ID
     */
    protected string $accountId;

    /**
     * @var float Current balance of bank account.
     */
    protected float $balance = 0.00;

    /**
     * AssetAccount constructor.
     *
     * @param string $accountId Account ID
     *
     * @todo Hook this up to an API or a database table for pulling the balance.
     */
    public function __construct(string $accountId)
    {
        $this->accountId = $accountId;
        // TODO: pull the balance from an API or database table, based on the account ID provided
    }

    /**
     * Adds the provided amount into the balance.
     *
     * @param int $amount
     *
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     */
    public function deposit($amount = 0): void
    {
        Helpers::validatePositiveNumber($amount);

        $this->balance += $amount;
    }

    /**
     * Subtracts the provided amount from the balance.
     *
     * @param int $amount
     *
     * @throws NonSufficientFundsException
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     */
    public function withdraw($amount = 0): void
    {
        Helpers::validatePositiveNumber($amount);

        if ($this->balance - $amount < 0) {
            throw new NonSufficientFundsException($this->balance, $amount);
        }
        $this->balance -= $amount;
    }

    /**
     * Gets the current balance.
     *
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * Initiate a transfer between bank accounts with the provided amount.
     *
     * @param string $fromAccountId Source bank account ID with funds to transfer.
     * @param string $toAccountId Destination bank account ID to receive funds.
     * @param float $amount Amount to transfer.
     *
     * @throws \AccountApp\Exceptions\NonSufficientFundsException
     * @throws \AccountApp\Exceptions\InvalidTransferAccountException
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     */
    public function transfer(string $fromAccountId, string $toAccountId, float $amount): void
    {
        $fromOther = $fromAccountId !== $this->accountId;
        $toOther = $toAccountId !== $this->accountId;
        if ($fromOther && $toOther) {
            throw new InvalidTransferAccountException($fromAccountId, $toAccountId);
        }

        Helpers::validatePositiveNumber($amount);

        if ($toOther) {
            $this->transferToAccount($fromAccountId, $amount);
        } else {
            $this->transferFromAccount($toAccountId, $amount);
        }
    }

    /**
     * Transfers the provided amount from the provided account.
     *
     * @param string $accountId Source account with the funds to transfer.
     * @param float $amount Amount to transfer.
     *
     * @throws \AccountApp\Exceptions\NonSufficientFundsException
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     */
    protected function transferFromAccount(string $accountId, float $amount): void
    {
        $account = new AssetAccount($accountId);
        $account->withdraw($amount);
        $this->deposit($amount);
    }

    /**
     * Transfer the provided amount to the provided account.
     *
     * @param string $accountId Destination account to receive the funds.
     * @param float $amount Amount to transfer.
     *
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     * @throws \AccountApp\Exceptions\NonSufficientFundsException
     */
    protected function transferToAccount(string $accountId, float $amount): void
    {
        $this->withdraw($amount);
        $account = new AssetAccount($accountId);
        $account->deposit($amount);
    }
}
