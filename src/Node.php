<?php

namespace SortedLinkedList;

final class Node
{
    public int|string $value;
    public ?Node $prev = null;
    public ?Node $next = null;
    public function __construct(int|string $value)
    {
        $this->value = $value;
    }
}
