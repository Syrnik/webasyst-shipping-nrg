<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license BSD 2.0
 */

namespace SergeR\Util\EvalMath\Methods;


class Maximum extends AbstractMethod
{
    protected $name='max';

    public function evaluate(...$args)
    {
        return max($args);
    }
}