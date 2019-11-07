<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license BSD 2.0
 */

namespace SergeR\Util\EvalMath\Tests;

use PHPUnit\Framework\TestCase;
use SergeR\Util\EvalMath\Methods\Conditional;
use SergeR\Util\EvalMath\Methods\Maximum;
use SergeR\Util\EvalMath\Methods\Minimum;

class MethodsTest extends TestCase
{
    public function testConditional()
    {
        $m = new Conditional();
        $this->assertEquals('if', $m->getName());
        $this->assertEquals([3], $m->getArgumentCount());
        $this->assertEquals(1, $m->evaluate(true, 1, 0));
        $this->assertEquals(0, $m->evaluate(false, 1, 0));
    }

    public function testMaximum()
    {
        $m = new Maximum();
        $this->assertEquals('max', $m->getName());
        $this->assertEquals([-1], $m->getArgumentCount());
        $this->assertEquals(1, $m->evaluate(1, 0));
        $this->assertEquals(10, $m->evaluate(false, 1, 0, 10));
        $this->assertEquals(1, $m->evaluate(1));
    }

    public function testMinimum()
    {
        $m = new Minimum();
        $this->assertEquals('min', $m->getName());
        $this->assertEquals([-1], $m->getArgumentCount());
        $this->assertEquals(0, $m->evaluate(1, 0));
        $this->assertEquals(0, $m->evaluate(false, 1, 0, 10));
        $this->assertEquals(1, $m->evaluate(1));
    }
}