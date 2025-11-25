<?php

namespace App\Core\Queue;

/**
 * Class JobSerializer
 * 
 * Handles serialization and deserialization of jobs for queue storage.
 */
class JobSerializer
{
    /**
     * Serialize a job for storage in the queue.
     *
     * @param string $jobClass Fully qualified job class name
     * @param array $data Job data payload
     * @return array Serialized job data
     */
    public static function serialize(string $jobClass, array $data): array
    {
        return [
            'job' => $jobClass,
            'data' => json_encode($data),
            'attempts' => 0,
            'created_at' => time(),
        ];
    }

    /**
     * Deserialize a job from queue storage.
     *
     * @param array $payload Serialized job data
     * @return object|null Instantiated job object or null on failure
     */
    public static function deserialize(array $payload): ?object
    {
        try {
            $jobClass = $payload['job'] ?? null;

            if (!$jobClass || !class_exists($jobClass)) {
                throw new \RuntimeException("Job class not found: {$jobClass}");
            }

            $data = json_decode($payload['data'] ?? '[]', true);

            $job = new $jobClass();

            if (method_exists($job, 'setData')) {
                $job->setData($data);
            }

            return $job;
        } catch (\Throwable $e) {
            error_log("Failed to deserialize job: " . $e->getMessage());
            return null;
        }
    }
}
