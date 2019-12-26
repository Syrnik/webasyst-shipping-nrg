<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2019
 * @license Webasyst
 */

namespace Syrnik\WaShippingUtils;

/**
 * Trait JsonResponseTrait
 * @package Syrnik\WaShippingUtils
 */
trait JsonResponseTrait
{
    /**
     * @param mixed $data
     * @throws \waException
     */
    protected function sendJsonData($data)
    {
        $response = array(
            'status' => 'ok',
            'data'   => $data,
        );
        $this->sendJsonResponse($response);
    }

    /**
     * @param mixed $response
     * @throws \waException
     */
    protected function sendJsonResponse($response)
    {
        wa()->getResponse()->addHeader('Content-Type', 'application/json')->sendHeaders();
        echo \waUtils::jsonEncode($response);
        exit;
    }

    /**
     * @param mixed $error
     * @throws \waException
     */
    protected function sendJsonError($error)
    {
        $response = array(
            'status' => 'fail',
            'errors' => array($error),
        );
        $this->sendJsonResponse($response);
    }
}