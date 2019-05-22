<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 */

namespace Syrnik\WaShippingUtils;

class CalcTotalCostException extends \RuntimeException
{
    protected $formula;
    protected $formula_vars = [];

    final public function setFormula($formula = '')
    {
        $this->formula = $formula;
        return $this;
    }

    final public function setFormulaVars(array $formula_vars = [])
    {
        $this->formula_vars = $formula_vars;
        return $this;
    }

    final public function getFormula()
    {
        return $this->formula;
    }

    final public function getFormulaVars()
    {
        return $this->formula_vars;
    }
}
