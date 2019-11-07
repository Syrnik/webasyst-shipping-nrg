<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license BSD 2.0
 */

namespace SergeR\Util\EvalMath;


use ArrayAccess;
use Countable;
use RuntimeException;
use SergeR\Util\EvalMath\Methods\AbstractMethod;

class MethodsRegistry implements ArrayAccess, Countable
{
    protected $methods = [];

    public function set(AbstractMethod $method)
    {
        $this->methods[$method->getName()] = clone $method;
        return $this;
    }

    public function unsetByName($name)
    {
        unset($this->methods[$name]);
        return $this;
    }

    /**
     * @param $name
     * @return AbstractMethod|null
     */
    public function findByName($name)
    {
        return (isset($this->methods[$name]) and $this->methods[$name] instanceof AbstractMethod) ? $this->methods[$name] : null;
    }

    public function offsetExists($offset)
    {
        return isset($this->methods[$offset]);
    }

    public function offsetGet($offset)
    {
        $m = $this->findByName($offset);
        return $m ? $m->getArgumentCount() : null;
    }

    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('Use set() method instead');
    }

    public function offsetUnset($offset)
    {
        $this->unsetByName($offset);
    }

    public function count()
    {
        return count($this->methods);
    }
}