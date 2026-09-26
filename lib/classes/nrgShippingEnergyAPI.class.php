<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2025
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class nrgShippingEnergyAPI
{
    private const API_URL = 'https://api2.nrg-tk.pro/v2/';

    /** Сколько символов тела ответа писать в лог (ответы бывают огромными, например список городов) */
    private const LOG_RESPONSE_LIMIT = 2000;

    private LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

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
        $this->_query($net, self::API_URL . $path, $params, waNet::METHOD_GET);

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
        $this->_query($net, self::API_URL . $path, $params, waNet::METHOD_POST);

        return $this->_parseResponse($net);
    }

    /**
     * Выполняет запрос, записывая в лог запрос, код и тело ответа (тело — усечённым)
     *
     * @param waNet $net
     * @param string $url
     * @param array $params
     * @param string $method
     * @return void
     * @throws waException
     * @throws waNetException
     * @throws waNetTimeoutException
     */
    private function _query(waNet $net, string $url, array $params, string $method): void
    {
        $this->logger->info('API ТК «Энергия»: {method} {url}, параметры: {params}', [
            'method' => $method,
            'url'    => $url,
            'params' => json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        try {
            $net->query($url, $params, $method);
        } catch (waException $e) {
            $this->logger->error('API ТК «Энергия»: ошибка запроса {url} ({class}): {message}', [
                'url'     => $url,
                'class'   => get_class($e),
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }

        $response = (string)$net->getResponse(true);
        if (mb_strlen($response) > self::LOG_RESPONSE_LIMIT) {
            $response = mb_substr($response, 0, self::LOG_RESPONSE_LIMIT) . '… (обрезано, всего ' . strlen($response) . ' байт)';
        }
        $this->logger->info('API ТК «Энергия»: HTTP {code}, ответ: {response}', [
            'code'     => $net->getResponseHeader('http_code'),
            'response' => $response,
        ]);
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
