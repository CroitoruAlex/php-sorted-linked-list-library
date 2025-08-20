<?php

declare(strict_types=1);

namespace SortedLinkedList;

use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;
use InvalidArgumentException;

final class SortedLinkedList implements IteratorAggregate, Countable, JsonSerializable
{
    private string $type;
    private bool $ascending;
    private bool $unique;
    private ?Node $head = null;
    private ?Node $tail = null;
    private int $count = 0;

    private function __construct(string $type, bool $ascending = true, bool $unique = false)
    {
        if (!\in_array($type, ['int', 'string'], true)) {
            throw new InvalidArgumentException("Type must be 'int' or 'string'");
        }

        $this->type = $type;
        $this->ascending = $ascending;
        $this->unique = $unique;
    }

    public static function forInt(bool $ascending = true, bool $unique = false): self
    {
        return new self('int', $ascending, $unique);
    }

    public static function forString(bool $ascending = true, bool $unique = false): self
    {
        return new self('string', $ascending, $unique);
    }

    public static function fromIterable(
        iterable $values,
        ?string $type = null,
        bool $ascending = true,
        bool $unique = false
    ): self {
        $detectedType = $type ?? self::detectType($values);
        $list = new self($detectedType, $ascending, $unique);

        foreach ($values as $v) {
            $list->add($v);
        }

        return $list;
    }

    public function getIterator(): Traversable
    {
        $current = $this->head;
        while ($current !== null) {
            yield $current->value;
            $current = $current->next;
        }
    }

    public function count(): int
    {
        return $this->count;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        $array = [];
        foreach ($this as $v) {
            $array[] = $v;
        }

        return $array;
    }

    public function add(int|string $value): void
    {
        $this->assertType($value);

        if ($this->unique && $this->contains($value)) {
            return;
        }

        $node = new Node($value);

        if ($this->head === null) {
            $this->head = $this->tail = $node;
            $this->count = 1;
            return;
        }

        if ($this->compare($value, $this->head->value) <= 0) {
            $this->insertBefore($node, $this->head);
            return;
        }

        if ($this->compare($value, $this->tail->value) >= 0) {
            $this->insertAfter($node, $this->tail);
            return;
        }

        $current = $this->head;
        while ($current !== null) {
            if ($this->compare($value, $current->value) <= 0) {
                $this->insertBefore($node, $current);
                return;
            }
            $current = $current->next;
        }
    }

    public function remove(int|string $value): bool
    {
        $this->assertType($value);

        $current = $this->head;
        while ($current !== null) {
            $cmp = $this->compare($value, $current->value);
            if ($cmp === 0) {
                $this->unlink($current);
                return true;
            }

            if (($this->ascending && $cmp < 0) || (!$this->ascending && $cmp > 0)) {
                return false;
            }
            $current = $current->next;
        }

        return false;
    }

    public function contains(int|string $value): bool
    {
        $this->assertType($value);

        $current = $this->head;
        while ($current !== null) {
            $cmp = $this->compare($value, $current->value);
            if ($cmp === 0) {
                return true;
            }
            if (($this->ascending && $cmp < 0) || (!$this->ascending && $cmp > 0)) {
                return false;
            }
            $current = $current->next;
        }

        return false;
    }

    public function first(): int|string|null
    {
        return $this->head?->value;
    }

    public function last(): int|string|null
    {
        return $this->tail?->value;
    }

    public function clear(): void
    {
        $this->head = $this->tail = null;
        $this->count = 0;
    }

    public function isAscending(): bool
    {
        return $this->ascending;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function type(): string
    {
        return $this->type;
    }

    private function insertBefore(Node $node, Node $target): void
    {
        $node->next = $target;
        $node->prev = $target->prev;

        if ($target->prev !== null) {
            $target->prev->next = $node;
        } else {
            $this->head = $node;
        }

        $target->prev = $node;
        $this->count++;
    }

    private function insertAfter(Node $node, Node $target): void
    {
        $node->prev = $target;
        $node->next = $target->next;

        if ($target->next !== null) {
            $target->next->prev = $node;
        } else {
            $this->tail = $node;
        }

        $target->next = $node;
        $this->count++;
    }

    private function unlink(Node $node): void
    {
        if ($node->prev !== null) {
            $node->prev->next = $node->next;
        } else {
            $this->head = $node->next;
        }

        if ($node->next !== null) {
            $node->next->prev = $node->prev;
        } else {
            $this->tail = $node->prev;
        }

        $node->next = $node->prev = null;
        $this->count--;
    }

    private static function detectType(iterable $values): string
    {
        foreach ($values as $value) {
            if (\is_int($value)) {
                return 'int';
            }
            if (\is_string($value)) {
                return 'string';
            }
            throw new InvalidArgumentException('Values must be int or string only.');
        }

        return 'string';
    }

    private function assertType(int|string $value): void
    {
        if ($this->type === 'int' && !\is_int($value)) {
            throw new InvalidArgumentException('This list only accepts int values.');
        }
        if ($this->type === 'string' && !\is_string($value)) {
            throw new InvalidArgumentException('This list only accepts string values.');
        }
    }

    private function compare(int|string $a, int|string $b): int
    {
        if ($this->type === 'int') {
            $cmp = $a <=> $b;
        } else {
            $cmp = strcmp((string)$a, (string)$b);
            $cmp = $cmp <=> 0;
        }

        return $this->ascending ? $cmp : -$cmp;
    }
}
