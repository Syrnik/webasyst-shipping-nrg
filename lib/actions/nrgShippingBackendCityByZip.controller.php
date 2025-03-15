<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2025
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

declare(strict_types=1);

/**
 * @Controller
 */
final class nrgShippingBackendCityByZipController extends waJsonController
{
    private string $zip;
    private nrgShipping $plugin;

    protected function preExecute()
    {
        parent::preExecute();
        $this->initZip();
        if (empty($this->plugin)) {
            $this->setError('Не найден инстанс плагина');
        }
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if ($this->errors) {
            return;
        }

        $this->response = $this->queryAPI();
    }

    /**
     * @return array
     * @todo add caching
     */
    private function queryAPI(): array
    {
        try {
            $result = $this->plugin->getEnergyAPI()->search_city($this->zip);
        } catch (waNetTimeoutException $e) {
            $this->setError('Сервер API ТК Энергия не отвечает, timeout.', $e->getCode());
            return [];
        } catch (waNetException $e) {
            $this->setError('Ошибка при обращении к северу API ТК Энергия', $e->getCode());
            return [];
        } catch (waException $e) {
            $this->setError($e->getMessage(), $e->getCode());
            return [];
        }

        if (!empty($result['error'])) {
            if (($result['error']['code'] ?? '') === 'NotFound') {
                $this->setError('Не найден город с указанным почтовым индексом');
                return [];
            }
            $this->setError($result['error']['message'] ?? 'Неизвестная ошибка', $result['error']['code'] ?? '');
            return [];
        }

        return $result;
    }

    /**
     * @return void
     */
    private function initZip(): void
    {
        $zip = (string)$this->getRequest()::get('zip', '', waRequest::TYPE_STRING_TRIM);
        if (empty($zip)) {
            $this->setError('Укажите почтовый индекс');
            return;
        }
        if (!preg_match('/^\d{6}$/', $zip)) {
            $this->setError('Неверный формат почтового индекса');
            return;
        }
        $this->zip = $zip;
    }

    /**
     * @param nrgShipping $plugin
     * @return void
     */
    public function setPlugin(nrgShipping $plugin): void
    {
        $this->plugin = $plugin;
    }
}
