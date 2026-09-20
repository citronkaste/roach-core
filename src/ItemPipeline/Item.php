<?php

declare(strict_types=1);

/**
 * Copyright (c) 2024 Kai Sassnowski
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/roach-php/roach
 */

namespace RoachPHP\ItemPipeline;

use RoachPHP\Support\Droppable;

final class Item implements ItemInterface
{
    use Droppable;

    public function __construct(private array $data)
    {
    }

    #[\Override()]
    public function all(): array
    {
        return $this->data;
    }

    #[\Override()]
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    #[\Override()]
    public function set(string $key, mixed $value): ItemInterface
    {
        $this->data[$key] = $value;

        return $this;
    }

    #[\Override()]
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    #[\Override()]
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    #[\Override()]
    public function offsetGet(mixed $offset): mixed
    {
        /** @psalm-suppress MixedReturnStatement */
        return $this->data[$offset];
    }

    #[\Override()]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        /** @psalm-suppress PossiblyNullArrayOffset */
        $this->data[$offset] = $value;
    }

    #[\Override()]
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }
}
