<?php

declare(strict_types=1);

namespace AccountApp\Exceptions;

use Exception;
use Throwable;

class InvalidTransferAccountException extends Exception
{
    public function __construct(
        string $fromAccountId,
        string $toAccountId,
        string $message = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        $failMessage = "Neither the source account, {$fromAccountId}, nor the destination account, {$toAccountId}, belong to you, so you cannot initiate a transfer between them.";
        parent::__construct($failMessage . PHP_EOL . $message, $code, $previous);
    }
}
