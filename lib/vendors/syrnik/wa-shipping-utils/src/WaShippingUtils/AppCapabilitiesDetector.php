<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2020
 * @license MIT
 */

namespace Syrnik\WaShippingUtils;

/**
 * Методы для использования в наследниках waShipping.
 *
 * Trait AppCapabilitiesDetector
 * @package Syrnik\WaShippingUtils
 * @method \waAppShipping getAdapter()
 */
trait AppCapabilitiesDetector
{
    /**
     * @return bool
     */
    protected function isAppSupportsSync()
    {
        try {
            return !empty($this->getAdapter()->getAppProperties('sync'));
        } catch (\waException $e) {
            return false;
        }
    }

    /**
     * Поддержка приложением передачи габаритов отправления
     *
     * @return string
     */
    protected function getAppDimensionSupport()
    {
        try {
            $dims = $this->getAdapter()->getAppProperties('dimensions');
        } catch (\waException $e) {
            return 'not_supported';
        }

        if ($dims === null) {
            return 'not_supported';
        } elseif ($dims === false) {
            return 'not_set';
        } elseif ($dims === true) {
            return 'no_external';
        }

        return 'supported';
    }
}
