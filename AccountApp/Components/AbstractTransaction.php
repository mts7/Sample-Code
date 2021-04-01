<?php

declare(strict_types=1);

namespace AccountApp\Components;

/**
 * Class AbstractTransaction
 *
 * @package AccountApp\Components
 */
abstract class AbstractTransaction
{
    /**
     * @var float The amount of the transaction.
     */
    protected float $amount;

    /**
     * @var string The payee of the transaction.
     */
    protected string $payee;

    /**
     * @var string|null The date of the transaction, defaulting to now.
     */
    protected ?string $date = null;

    /**
     * @var string|null Spending category.
     */
    protected ?string $category = null;

    /**
     * AbstractTransaction constructor.
     */
    public function __construct()
    {
        if ($this->date === null) {
            $this->date = date('Y-m-d H:i:s');
        }

        if ($this->category === null) {
            $this->category = 'Miscellaneous';
        }
    }
}
