<?php

declare(strict_types=1);

namespace AccountApp\Components;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class CheckRegistry
 *
 * @package AccountApp\Components
 */
class CheckRegistry extends AbstractTransaction
{
    /**
     * @var string The memo provided or empty string.
     */
    protected string $memo = '';

    /**
     * @var int|null The check number to use (or null for the next check number).
     */
    protected ?int $checkNumber = null;

    /**
     * CheckRegistry constructor.
     *
     * @param array $params
     */
    public function __construct($params = [])
    {
        // assign legitimate parameters
        foreach ($params as $key => $value) {
            if (property_exists(self::class, $key)) {
                $this->{$key} = $value;
            } else {
                $class = self::class;
                throw new InvalidArgumentException("{$key} is not valid for {$class}.");
            }
        }

        parent::__construct();
    }

    /**
     * Gets the data from the object as an array with property names as the indices.
     *
     * @return array
     */
    public function getData(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);

        $result = [];
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $result[$propertyName] = $this->{$propertyName};
        }
        return $result;
    }
}
