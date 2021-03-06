<?php

namespace CatLab\Base\Collections;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Class Collection
 * @package CatLab\RESTResource\Collections
 */
class Collection implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var array
     */
    protected $data = array();

    protected function setCollectionValues(array $data)
    {
        $this->data = $data;
    }

    public function add($value)
    {
        $this[] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset ($this->data[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $index = count($this);
            $this->data[$index] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        $value = isset ($this->data[$offset]) ? $this->data[$offset] : null;
        unset ($this->data[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Return the next element without increasing position.
     * @return mixed|null
     */
    public function peek()
    {
        if (isset ($this->data[$this->position + 1])) {
            return $this->data[$this->position + 1];
        }
        return null;
    }

    /**
     * Reverse the collection.
     */
    public function reverse()
    {
        $this->data = array_reverse($this->data);
    }

    /**
     * Remove an element from the collection.
     * @param $entry
     * @return bool
     */
    public function remove($entry)
    {
        foreach ($this->data as $k => $v) {
            if ($v === $entry) {
                $associative = $this->isAssociative();

                unset ($this->data[$k]);
                if ($associative) {
                    $this->data = array_values($this->data);
                }

                return true;

            }
        }
        return false;
    }

    /**
     * Return the very first element.
     */
    public function first()
    {
        if (!is_array($this->data)) return $this->data;
        if (!count($this->data)) return null;
        reset($this->data);
        return $this->data[key($this->data)];
    }

    /**
     * Return the very last element.
     */
    public function last()
    {
        if (!is_array($this->data)) return $this->data;
        if (!count($this->data)) return null;
        end($this->data);
        return $this->data[key($this->data)];
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new CollectionIterator($this);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Filter and return a new collection.
     * @param callable $filter
     * @return self
     */
    public function filter(callable $filter)
    {
        $out = new static();

        $this->each(function ($value) use ($filter, $out) {
            if (call_user_func($filter, $value)) {
                $out->add($value);
            }
        });

        return $out;
    }

    /**
     * Filter and return a new collection.
     * @param callable $filter
     * @return void
     */
    public function each(callable $filter)
    {
        foreach ($this->data as $value) {
            call_user_func($filter, $value);
        }
    }

    /**
     * Execute a function on all members of the collection.
     * @param callable $filter      Filter that will be called on every element in the collection.
     * @param string $collection    Classname of collection that will be returned.
     * @return Collection
     */
    public function map(
        callable $filter,
        $collection = self::class
    ) {
        $out = new $collection();
        foreach ($this as $k => $v) {
            $out[$k] = call_user_func($filter, $v);
        }

        return $out;
    }

    /**
     * @param callable $sortFunction
     */
    public function sort(callable $sortFunction)
    {
        usort($this->data, $sortFunction);
    }

    protected function isAssociative()
    {
        return array_values($this->data) === $this->data;
    }
}