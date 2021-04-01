<?php

declare(strict_types=1);

namespace AccountApp\Objects;

use AccountApp\Base\AssetAccount;
use AccountApp\Components\CheckRegistry;

/**
 * Class CheckingAccount
 *
 * @package AccountApp
 */
class CheckingAccount extends AssetAccount
{
    /**
     * @var array Store all of the transactions in a single check register.
     */
    protected array $register = [];

    /**
     * CheckingAccount constructor.
     *
     * @param string $accountId
     *
     * @todo initialize the register for the provided account ID
     */
    public function __construct(string $accountId)
    {
        parent::__construct($accountId);
    }

    /**
     * Records a check in the register and withdraws the amount from the balance.
     *
     * @param float $amount Amount of transaction.
     * @param string $payee Recipient of funds.
     * @param string $memo Note of payment.
     * @param string|null $date Date of check, defaults to now.
     * @param int|null $checkNumber , Check number.
     *
     * @throws \AccountApp\Exceptions\NonSufficientFundsException
     * @throws \AccountApp\Exceptions\NegativeNotAllowedException
     */
    public function writeCheck(
        float $amount,
        string $payee,
        string $memo = '',
        string $date = null,
        int $checkNumber = null
    ): void {
        $checkNumber = $checkNumber ?? $this->getNextCheckNumber();

        $registry = new CheckRegistry(
            [
                'amount' => $amount,
                'payee' => $payee,
                'memo' => $memo,
                'date' => $date,
                'checkNumber' => $checkNumber,
            ]
        );
        $this->register[] = $registry;

        $this->withdraw($amount);
    }

    /**
     * Gets the check register transactions.
     *
     * @return CheckRegistry[]
     */
    public function getTransactions(): array
    {
        return $this->register;
    }

    /**
     * Gets the next check number based on the length of the checking account register.
     *
     * @return int
     */
    protected function getNextCheckNumber(): int
    {
        return count($this->register) + 1;
    }
}
