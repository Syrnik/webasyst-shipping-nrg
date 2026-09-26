<?php
/**
 * Адаптер хостового приложения для тестов.
 *
 * Настройки не заданные явно через applySettings() подтягиваются
 * waSystemPlugin::setSettings() из дефолтов lib/config/settings.php —
 * фикстуре достаточно хранить только то, что тест переопределяет.
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2026
 */
class waAppShippingFixture extends waAppShipping
{
    protected $settings = [
        1 => [],
    ];

    protected function init()
    {
        $this->app_id = 'testsuite';
    }

    /**
     * @param $plugin_id string
     * @param $key string
     * @return array
     */
    public function getSettings($plugin_id, $key)
    {
        if (array_key_exists($key, $this->settings)) {
            return $this->settings[$key];
        }

        return [];
    }

    /**
     * @param $plugin_id string
     * @param $key string
     * @param $name
     * @param $value
     * @return array
     */
    public function setSettings($plugin_id, $key, $name, $value)
    {
        $this->settings[$key][$name] = $value;
        return [];
    }

    /**
     * @param $key string
     * @param array $settings
     */
    public function applySettings($key, array $settings)
    {
        if (!array_key_exists($key, $this->settings)) {
            $this->settings[$key] = [];
        }

        $this->settings[$key] = array_merge($this->settings[$key], $settings);
    }
}
