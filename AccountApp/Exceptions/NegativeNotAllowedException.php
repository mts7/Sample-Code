<?php

declare(strict_types=1);

namespace AccountApp\Exceptions;

use Exception;
use Throwable;

/**
 * Class NegativeNotAllowedException
 * @package AccountApp\Exceptions
 */
class NegativeNotAllowedException extends Exception
{
    /**
     * NegativeNotAllowedException constructor.
     * @param float $amount Amount attempted to be used.
     * @param string $message An optional error message.
     * @param int $code An optional error code.
     * @param Throwable|null $previous A previously thrown exception.
     */
    public function __construct(float $amount, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $failMessage = "{$amount} is negative, which is not allowed; please provide a positive value.";
        parent::__construct($failMessage . PHP_EOL . $message, $code, $previous);
    }
}
