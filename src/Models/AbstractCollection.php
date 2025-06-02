<?php
namespace Nava\Dinlr\Models;

/**
 * Abstract collection class
 */
abstract class AbstractCollection implements \ArrayAccess, \Countable, \Iterator, \JsonSerializable
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * Create a new collection
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->setItems($items);
    }

    /**
     * Set the items in the collection
     *
     * @param array $items
     * @return self
     */
    public function setItems(array $items): self
    {
        $this->items    = [];
        $this->position = 0;

        foreach ($items as $item) {
            $this->add($item);
        }

        return $this;
    }

    /**
     * Add an item to the collection
     *
     * @param mixed $item
     * @return self
     */
    public function add($item): self
    {
        if (! $item instanceof $this->modelClass) {
            $item = new $this->modelClass($item);
        }

        $this->items[] = $item;
        return $this;
    }

    /**
     * Get all items in the collection
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get the first item in the collection
     *
     * @return mixed|null
     */
    public function first()
    {
        return $this->items[0] ?? null;
    }

    /**
     * Get the collection as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];

        foreach ($this->items as $item) {
            $array[] = $item->toArray();
        }

        return $array;
    }

    /**
     * Convert the collection to JSON
     *
     * @param int $options
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Count the number of items in the collection (Countable)
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get the current item (Iterator)
     *
     * @return mixed
     */
    public function current()
    {
        return $this->items[$this->position];
    }

    /**
     * Get the current position (Iterator)
     *
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Move to the next item (Iterator)
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Rewind the iterator (Iterator)
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Check if the current position is valid (Iterator)
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    /**
     * Check if the offset exists (ArrayAccess)
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Get the item at the offset (ArrayAccess)
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * Set the item at the offset (ArrayAccess)
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->add($value);
        } else {
            if (! $value instanceof $this->modelClass) {
                $value = new $this->modelClass($value);
            }

            $this->items[$offset] = $value;
        }
    }

    /**
     * Unset the item at the offset (ArrayAccess)
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * Serialize to JSON (JsonSerializable)
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
