<?php

declare(strict_types=1);

namespace AccountApp\Exceptions;

use Exception;
use Throwable;

/**
 * Class OverTheLimitException
 *
 * @package AccountApp\Exceptions
 */
class OverTheLimitException extends Exception
{
    /**
     * OverTheLimitException constructor.
     *
     * @param float $amount Requested transaction amount.
     * @param float $limit Liability limit.
     * @param string $message An optional message.
     * @param int $code An optional error code.
     * @param \Throwable|null $previous A previously-thrown exception.
     */
    public function __construct(
        float $amount,
        float $limit,
        string $message = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        $failMessage = "The transaction amount of {$amount} would cause the balance to exceed the {$limit} limit.";
        parent::__construct($failMessage . PHP_EOL . $message, $code, $previous);
    }
}
