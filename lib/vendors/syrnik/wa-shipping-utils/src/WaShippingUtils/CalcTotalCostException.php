<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018-2021
 */

namespace Syrnik\WaShippingUtils;

/**
 * Class CalcTotalCostException
 * @package Syrnik\WaShippingUtils
 */
class CalcTotalCostException extends \RuntimeException
{
    /**
     * @var
     */
    protected $formula;

    /**
     * @var array
     */
    protected $formula_vars = [];

    /**
     * @param string $formula
     * @return $this
     */
    final public function setFormula(string $formula = ''): CalcTotalCostException
    {
        $this->formula = $formula;
        return $this;
    }

    /**
     * @param array $formula_vars
     * @return $this
     */
    final public function setFormulaVars(array $formula_vars = []): CalcTotalCostException
    {
        $this->formula_vars = $formula_vars;
        return $this;
    }

    /**
     * @return mixed
     */
    final public function getFormula()
    {
        return $this->formula;
    }

    /**
     * @return array
     */
    final public function getFormulaVars(): array
    {
        return $this->formula_vars;
    }
}
