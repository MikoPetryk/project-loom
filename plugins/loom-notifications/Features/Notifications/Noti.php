<?php
/**
 * Noti - PHP API Facade
 *
 * Provides static methods for triggering notifications from PHP.
 *
 * @package Loom\Noti\Features\Notifications
 * @since 1.0.0
 */



namespace Loom\Noti\Features\Notifications;

class Noti {

    public const SUCCESS = 'success';
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const INFO = 'info';
    public const LOG = 'log';

    public static function success(string $message, array $options = []): void {
        self::enqueue(self::SUCCESS, $message, $options);
    }

    public static function error(string $message, array $options = []): void {
        self::enqueue(self::ERROR, $message, $options);
    }

    public static function warning(string $message, array $options = []): void {
        self::enqueue(self::WARNING, $message, $options);
    }

    public static function info(string $message, array $options = []): void {
        self::enqueue(self::INFO, $message, $options);
    }

    public static function log(string $message, array $options = []): void {
        self::enqueue(self::LOG, $message, $options);
    }

    public static function progress(string $message, array $options = []): ProgressNotification {
        $id = uniqid('noti_progress_', true);
        $options['id'] = $id;
        $options['type'] = 'progress';
        $options['total'] = $options['total'] ?? 100;
        $options['current'] = $options['current'] ?? 0;

        self::enqueue('progress', $message, $options);

        return new ProgressNotification($id, $message, $options);
    }

    public static function enqueue(string $type, string $message, array $options = []): void {
        QueueManager::add([
            'type' => $type,
            'message' => $message,
            'options' => $options,
            'time' => time(),
        ]);
    }

    public static function getQueue(): array {
        return QueueManager::get();
    }

    public static function clearQueue(): void {
        QueueManager::clear();
    }

    public static function renderQueue(): void {
        $notifications = self::getQueue();

        if (empty($notifications)) {
            return;
        }

        self::clearQueue();

        $json = wp_json_encode($notifications);
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Noti !== 'undefined' && Noti.showQueued) {
                    Noti.showQueued({$json});
                }
            });
        </script>";
    }
}
