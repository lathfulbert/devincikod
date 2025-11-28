<?php

namespace Modules\SmsCore\Services;

class MessageDispatcherService
{
    protected MessageQueueService $queue;
    protected SmsSenderService $sender;

    public function __construct(MessageQueueService $queue, SmsSenderService $sender)
    {
        $this->queue = $queue;
        $this->sender = $sender;
    }

    public function dispatch(): array
    {
        $results = [];

        while (($message = $this->queue->dequeue()) !== null) {
            try {
                $response = $this->sender->send(
                    $message['to'],
                    $message['message'],
                    $message['sender_id'] ?? 'DEFAULT',
                    $message['options'] ?? []
                );

                $results[] = [
                    'message_id' => $message['id'],
                    'status' => 'sent',
                    'response' => $response
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'message_id' => $message['id'],
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    public function dispatchOne(): ?array
    {
        $message = $this->queue->dequeue();

        if ($message === null) {
            return null;
        }

        try {
            $response = $this->sender->send(
                $message['to'],
                $message['message'],
                $message['sender_id'] ?? 'DEFAULT',
                $message['options'] ?? []
            );

            return [
                'message_id' => $message['id'],
                'status' => 'sent',
                'response' => $response
            ];
        } catch (\Throwable $e) {
            return [
                'message_id' => $message['id'],
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }
}
