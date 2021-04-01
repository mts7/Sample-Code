<?php

declare(strict_types=1);

namespace AccountApp\Interfaces;

/**
 * Interface AssetAccountInterface
 * @package AccountApp
 */
interface AssetAccountInterface
{
    /**
     * Adds the amount provided to the balance.
     *
     * @param float $amount
     * @return mixed
     */
    public function deposit(float $amount): void;

    /**
     * Subtracts the amount provided from the balance.
     *
     * @param float $amount
     * @return mixed
     */
    public function withdraw(float $amount): void;

    /**
     * Gets the current balance.
     *
     * @return float
     */
    public function getBalance(): float;
}
