<?php

declare(strict_types=1);

namespace AccountApp\Interfaces;

/**
 * Interface LiabilityAccountInterface
 *
 * @package AccountApp
 */
interface LiabilityAccountInterface
{
    /**
     * Increases the amount owed.
     *
     * @param float $amount
     */
    public function borrow(float $amount): void;

    /**
     * Decreases the amount owed.
     *
     * @param float $amount
     */
    public function repay(float $amount): void;

    /**
     * Gets the balance of the account.
     *
     * @return float
     */
    public function getBalance(): float;

    /**
     * Gets the limit for the account.
     *
     * @return float
     */
    public function getLimit(): float;
}
