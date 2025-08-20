<?php


use PHPUnit\Framework\TestCase;
use SortedLinkedList\SortedLinkedList;

class SortedLinkedListTest extends TestCase
{
    public function testInvalidTypeRejected()
    {
        $this->expectException(\InvalidArgumentException::class);
        $list = SortedLinkedList::forInt();
        $list->add('float');
    }

    public function testAddIntegersSortedAscending()
    {
        $list = SortedLinkedList::forInt(ascending: true);

        $list->add(1);
        $list->add(3);
        $list->add(2);

        $this->assertSame([1,2,3], $list->toArray());
        $this->assertSame(1, $list->first());
        $this->assertSame(3, $list->last());
    }

    public function testAddStringsAndSort()
    {
        $list = SortedLinkedList::forString();

        $list->add('b');
        $list->add('a');
        $list->add('c');

        $this->assertSame(['a', 'b', 'c'], $list->toArray());
    }

    public function testAddIntegersSortedDescending()
    {
        $list = SortedLinkedList::forInt(ascending: false);

        $list->add(2);
        $list->add(3);
        $list->add(1);

        $this->assertSame([3,2,1], $list->toArray());
        $this->assertSame(3, $list->first());
        $this->assertSame(1, $list->last());
    }

    public function testAddUniqueValuesOnly()
    {
        $list = SortedLinkedList::forInt(unique: true);

        $list->add(1);
        $list->add(2);
        $list->add(1);

        $this->assertSame([1,2], $list->toArray());
        $this->assertSame(2, $list->count());
    }

    public function testRemoveExistingValue()
    {
        $list = SortedLinkedList::forInt();

        $list->add(1);
        $list->add(2);
        $list->add(3);

        $this->assertTrue($list->remove(2));
        $this->assertSame([1,3], $list->toArray());
    }

    public function testContainsValue()
    {
        $list = SortedLinkedList::forInt();

        $list->add(1);
        $list->add(2);

        $this->assertTrue($list->contains(2));
    }

    public function testClear()
    {
        $list = SortedLinkedList::forInt();

        $list->add(1);
        $list->add(2);

        $this->assertSame([1,2], $list->toArray());
        $this->assertSame(2, $list->count());
        $list->clear();

        $this->assertCount(0, $list);
    }

    public function testFromIterableWithIntegersSortedAscending()
    {
        $list = SortedLinkedList::fromIterable([5, 2, 9, 1]);

        $this->assertSame([1, 2, 5, 9], $list->toArray());
        $this->assertSame('int', $list->type());
        $this->assertTrue($list->isAscending());
    }

    public function testFromIterableWithStringsSorted()
    {
        $list = SortedLinkedList::fromIterable(['b', 'a', 'x', 'f']);

        $this->assertSame(['a', 'b', 'f', 'x'], $list->toArray());
        $this->assertSame('string', $list->type());
    }

    public function testFromIterableThrowsExceptionIfWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);

        SortedLinkedList::fromIterable([0.2, 1.1]);
    }

    public function testFromIterableWithEmptyInputDefaultsToStringType()
    {
        $list = SortedLinkedList::fromIterable([]);

        $this->assertSame([], $list->toArray());
        $this->assertSame('string', $list->type());
    }
}
