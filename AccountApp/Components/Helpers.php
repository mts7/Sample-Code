<?php

declare(strict_types=1);

namespace AccountApp\Components;

use AccountApp\Exceptions\NegativeNotAllowedException;

/**
 * Class Helpers
 *
 * @package AccountApp\Components
 */
class Helpers
{
    /**
     * Validate the amount provided is not negative, throwing an exception when negative.
     *
     * @param float $amount
     *
     * @throws NegativeNotAllowedException
     */
    public static function validatePositiveNumber(float $amount): void
    {
        if ($amount < 0) {
            throw new NegativeNotAllowedException($amount);
        }
    }
}
