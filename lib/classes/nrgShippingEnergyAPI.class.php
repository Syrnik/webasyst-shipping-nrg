<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2025
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

declare(strict_types=1);

final class nrgShippingEnergyAPI
{
    private const API_URL = 'https://api2.nrg-tk.ru/v2/';

    /**
     * @param string $zip
     * @return array
     * @throws waException
     * @throws waNetException
     * @throws waNetTimeoutException
     */
    public function search_city(string $zip): array
    {
        return $this->_get('search/city', ['zipCode' => $zip]);
    }

    /**
     * @param string $lang
     * @return array[]
     * @throws waException
     * @throws waNetException
     * @throws waNetTimeoutException
     */
    public function cities(string $lang = 'ru'): array
    {
        return $this->_get('cities', ['lang' => $lang]);
    }

    /**
     * @param array $params
     * @return array|array[]
     * @throws waException
     * @throws waNetException
     * @throws waNetTimeoutException
     */
    public function price(array $params): array
    {
        return $this->_post('price', $params);
    }

    /**
     * @param string $path
     * @param array $params
     * @param array $headers
     * @return array
     * @throws waNetTimeoutException
     * @throws waNetException
     * @throws waException
     */
    private function _get(string $path, array $params = [], array $headers = []): array
    {
        $net = new waNet(['expected_http_code' => [200, 400, 404, 500], 'verify' => false], $headers);
        $net->query(self::API_URL . $path, $params);
        return $this->_parseResponse($net);
    }

    /**
     * @param string $path
     * @param array $params
     * @param array $headers
     * @return array|mixed
     * @throws waException
     * @throws waNetException
     * @throws waNetTimeoutException
     */
    private function _post(string $path, array $params = [], array $headers = []): array
    {
        $net = new waNet(['expected_http_code' => [200, 400, 404, 500], 'request_format' => waNet::FORMAT_JSON, 'verify' => false], $headers);
        $net->query(self::API_URL . $path, $params, waNet::METHOD_POST);

        return $this->_parseResponse($net);
    }

    /**
     * @param waNet $net
     * @return array|mixed
     * @throws waNetTimeoutException
     * @throws waNetException
     * @throws waException
     */
    private function _parseResponse(waNet $net)
    {
        if ($net->getResponseHeader('http_code') === 200) {
            try {
                return json_decode($net->getResponse(true), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new waException($e->getMessage(), $e->getCode());
            }
        }

        $content_type = $net->getResponseHeader('content-type');
        if (stripos($content_type, 'application/json') === 0) {
            try {
                return [
                    'error' => json_decode($net->getResponse(true), true, 512, JSON_THROW_ON_ERROR)
                        + ['status' => $net->getResponseHeader('http_code')],
                ];
            } catch (JsonException $e) {
                throw new waException($e->getMessage(), $e->getCode());
            }
        }

        throw new waException($net->getResponse(), $net->getResponseHeader('http_code'));
    }
}
