<?php
/**
 * Progress Notification
 *
 * Represents a progress notification that can be updated.
 *
 * @package Loom\Noti\Features\Notifications
 * @since 1.0.0
 */



namespace Loom\Noti\Features\Notifications;

class ProgressNotification {

    private string $id;
    private string $message;
    private array $options;
    private int $total;
    private int $current;

    public function __construct(string $id, string $message, array $options = []) {
        $this->id = $id;
        $this->message = $message;
        $this->options = $options;
        $this->total = $options['total'] ?? 100;
        $this->current = $options['current'] ?? 0;
    }

    public function getId(): string {
        return $this->id;
    }

    public function update(array $data): void {
        if (isset($data['current'])) {
            $this->current = (int) $data['current'];
        }

        if (isset($data['message'])) {
            $this->message = $data['message'];
        }

        QueueManager::add([
            'type' => 'progress_update',
            'id' => $this->id,
            'current' => $this->current,
            'total' => $this->total,
            'message' => $this->message,
            'time' => time(),
        ]);
    }

    public function complete(string $message = null): void {
        QueueManager::add([
            'type' => 'progress_complete',
            'id' => $this->id,
            'message' => $message ?? $this->message,
            'time' => time(),
        ]);
    }

    public function fail(string $message): void {
        QueueManager::add([
            'type' => 'progress_fail',
            'id' => $this->id,
            'message' => $message,
            'time' => time(),
        ]);
    }

    public function getProgress(): float {
        if ($this->total === 0) {
            return 0;
        }
        return ($this->current / $this->total) * 100;
    }
}
