<?php

namespace Bouh\Paginator;

trait Immutable
{
	private $values;

	public function __set($property, $value)
    {
        throw new \Exception($property . ' is immutable');
    }


    public function __unset($property)
    {
        throw new \Exception($property . ' is immutable');
    }


    public function __get($property)
    {
        if (isset($this->$property) === false) {
            throw new \Exception('Undefined property: ' . get_class($this) . '::' . $property);
        }

        return $this->values[$property];
    }


    public function __isset($property)
    {
        return array_key_exists($property, $this->values);
    }
}
