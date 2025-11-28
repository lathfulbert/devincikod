<?php

namespace Modules\SmsCore\Services;

class MessageQueueService
{
    protected array $queue = [];
    protected string $storage = 'memory'; // memory, database, redis

    public function enqueue(array $message): void
    {
        $message['id'] = uniqid('msg_');
        $message['status'] = 'pending';
        $message['created_at'] = date('Y-m-d H:i:s');

        $this->queue[] = $message;
    }

    public function dequeue(): ?array
    {
        if (empty($this->queue)) {
            return null;
        }

        return array_shift($this->queue);
    }

    public function peek(): ?array
    {
        return $this->queue[0] ?? null;
    }

    public function size(): int
    {
        return count($this->queue);
    }

    public function getAll(): array
    {
        return $this->queue;
    }

    public function clear(): void
    {
        $this->queue = [];
    }
}
