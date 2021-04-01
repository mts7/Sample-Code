<?php

declare(strict_types=1);

namespace AccountApp\Exceptions;

use Exception;
use Throwable;

/**
 * Class NonSufficientFundsException
 * @package AccountApp
 *
 * Provide a custom error message with the balance and amount from the account and transaction.
 */
class NonSufficientFundsException extends Exception
{
    /**
     * NonSufficientFundsException constructor.
     * @param float $balance The current available balance.
     * @param float $amount The amount requested for withdrawal.
     * @param string $message An optional message.
     * @param int $code An optional error code.
     * @param Throwable|null $previous A previously thrown exception.
     */
    public function __construct(float $balance, float $amount, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $balanceMessage = "Your balance of ${balance} does not have ${amount} available for withdrawal.";
        parent::__construct($balanceMessage . PHP_EOL . $message, $code, $previous);
    }
}
