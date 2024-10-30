<?php
namespace CompygoSocialFeed\Model;

class Logger
{
    const ERROR_API = 'api';
    const ERROR_SYSTEM = 'system';
    const ERROR_DB = 'db';
    const ERROR_VALIDATION = 'validation';
    const OPTION_PATH = CGUSF_PREFIX . 'logs';
    const DATE_FORMAT = 'd-m-Y H:i:s';

    static function addLog($type, $message, $caller = null)
    {
        $log = [
            'time' => date(self::DATE_FORMAT),
            'type' => $type,
            'message' => $message,
        ];

        self::writeLogs($log);
    }

    static function writeLogs($log)
    {
        if (is_array($log)) {
            $logs = get_option(self::OPTION_PATH);

            if ($logs) {
                array_unshift($logs, $log);
            } else {
                $logs = [$log];
            }

            self::clearExpiredLogs($logs);
        }
    }

    static function clearExpiredLogs($logs)
    {
        $freshLogs = [];

        if (is_array($logs)) {
            foreach ($logs as $log) {
                $date = \DateTime::createFromFormat(self::DATE_FORMAT, $log['time']);
                if ($date !== false) {
                    if ($date->getTimestamp() > strtotime('-7 days')) {
                        $freshLogs[] = $log;
                    }
                }
            }

            update_option(self::OPTION_PATH, $freshLogs);
        }

        return $freshLogs;
    }

    /**
     * @param $results
     * @return void
     */
    static function addDbLog($results)
    {
        if ($results === false && !empty($wpdb->last_error)) {
            $bt = debug_backtrace();
            Logger::addLog(Logger::ERROR_DB, $wpdb->last_error, $bt[1]);
        }
    }

    static function getLogs()
    {
        return get_option(self::OPTION_PATH);
    }
}