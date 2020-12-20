<?php
declare(strict_types=1);

namespace FastRoute;

use ArrayAccess;
use RuntimeException;

/**
 * Result Object
 *
 * @implements ArrayAccess<int, mixed>
 */
class Result implements ArrayAccess
{
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    /** @var bool */
    protected $matched = false;

    /** @var RouteInterface */
    protected $route;

    /** @var mixed[] */
    protected $result = [];

    /** @var int */
    protected $status = self::NOT_FOUND;

    /** @var mixed */
    protected $handler;

    /** @var mixed[] */
    protected $args = [];

    /** @var string[] */
    protected $allowedMethods = [];

    public static function createFound(RouteInterface $route): Result
    {
        $self = new self();
        $self->status = self::FOUND;
        $self->route = $route;
        $self->handler = $route->handler();

        return $self;
    }

    public static function createNotFound(): Result
    {
        $self = new self();
        $self->result = [self::NOT_FOUND];
        $self->status = self::NOT_FOUND;

        return $self;
    }

    /**
     * @param string[] $allowedMethods
     */
    public static function createMethodNotAllowed(array $allowedMethods): Result
    {
        $self = new self();
        $self->result = [self::METHOD_NOT_ALLOWED, $allowedMethods];
        $self->status = self::METHOD_NOT_ALLOWED;
        $self->allowedMethods = $allowedMethods;

        return $self;
    }

    /**
     * @param mixed[] $result Result
     */
    public static function fromArray(array $result): Result
    {
        $self = new self();
        $self->result = $result;
        $self->status = $result[0];

        if ($result[0] === self::FOUND) {
            $self->handler = $result[1];
            $self->args = $result[2];
            $self->route = $result[3];
        }

        return $self;
    }

    /**
     * @return mixed
     */
    public function handler()
    {
        if (! isset($this->result[1])) {
            return null;
        }

        return $this->result[1];
    }

    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function args()
    {
        if (! isset($this->result[2])) {
            return [];
        }

        return $this->result[2];
    }

    public function routeMatched(): bool
    {
        return $this->result[0] === self::FOUND;
    }

    public function methodNotAllowed(): bool
    {
        return $this->result[0] === self::METHOD_NOT_ALLOWED;
    }

    public function routeNotFound(): bool
    {
        return $this->result[0] === self::NOT_FOUND;
    }

    /**
     * @param mixed $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->result[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->result[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException(
            'You can\'t mutate the state of the result'
        );
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        throw new RuntimeException(
            'You can\'t mutate the state of the result'
        );
    }

    /**
     * Gets the legacy array
     *
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->result;
    }
}
