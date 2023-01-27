<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2020-2021
 * @license MIT
 */
declare(strict_types=1);

namespace Syrnik\WaShippingUtils;

/**
 * Методы для использования в наследниках waShipping.
 *
 * @deprecated
 * Trait AppCapabilitiesDetector
 * @package Syrnik\WaShippingUtils
 * @method \waAppShipping getAdapter()
 */
trait AppCapabilitiesDetector
{
    /**
     * @return bool
     */
    protected function isAppSupportsSync(): bool
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
    protected function getAppDimensionSupport(): string
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
