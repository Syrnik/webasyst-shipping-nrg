<?php
/**
 * @copyright     Copyright (c) 2018, Serge Rodovnichenko, <sergerod@gmail.com>
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace SergeR;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class ProcessLogger extends AbstractLogger
{

    /** @var string|null Current log level. If null, logging is disabled */
    protected $level;

    /** @var array Array of levels to log */
    protected $levels_to_log = [];

    protected $messages;
    protected $start;
    protected $mem;
    protected $timers = [];

    public function __construct($log_level = null)
    {
        $this->setLogLevel($log_level);
    }

    /**
     * Current log level
     *
     * @return null|string
     */
    public function getLogLevel()
    {
        return $this->level;
    }

    /**
     * Sets current log level
     *
     * @param string|null $log_level
     * @return $this
     */
    public function setLogLevel($log_level)
    {
        // descending list of levels
        $all_levels = array(
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG
        );
        $this->level = $log_level;
        $this->levels_to_log = [];
        if ($log_level && in_array($log_level, $all_levels)) {
            $idx = array_search($log_level, $all_levels);
            $this->levels_to_log = array_slice($all_levels, 0, $idx + 1);
            $this->level = $log_level;

        }
        return $this;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        if (!in_array($level, $this->levels_to_log)) {
            return;
        }

        if (!$this->start) {
            $this->start = microtime(true);
            $this->mem = memory_get_peak_usage();
        }

        if (!$this->messages) {
            $this->messages = array('*********************************************');
        }
        $this->messages[] = '* ' . $this->interpolate($message, $context);
    }

    /**
     * Generates a concatenation of all messages and flushes times and message storage
     *
     * @return string
     */
    public function flush()
    {
        if (empty($this->messages)) {
            return '';
        }

        $bytes_used = memory_get_peak_usage() - $this->mem;


        $this->messages[] = sprintf(
            '* = Total execution time %0.2F seconds, total used memory: %s',
            microtime(true) - $this->start,
            $this->formatBytes($bytes_used)
        );
        $this->messages[] = '*********************************************';
        $result = implode("\n", $this->messages);
        $this->messages = $this->start = null;

        return $result;
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message
     * @param array $context
     * @return string
     */
    protected function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            } else {
                $replace['{' . $key . '}'] = var_export($val, true);
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    /**
     * @param $bytes
     * @param int $precision
     * @return string
     */
    protected function formatBytes($bytes, $precision = 3)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return rtrim(rtrim(number_format(round($bytes, $precision), 2, '.', ''), '0'), '.') . ' ' . $units[$pow];
    }
}
