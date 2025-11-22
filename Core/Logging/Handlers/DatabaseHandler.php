<?php

namespace App\Core\Logging\Handlers;

use App\Core\Database\Database;

class DatabaseHandler extends AbstractHandler
{
    protected $db;
    protected string $table;

    public function __construct(string $table = 'logs', int $level = 0)
    {
        parent::__construct($level);
        $this->table = $table;
        $this->db = Database::getInstance();
    }

    protected function write(array $record): void
    {
        $sql = "INSERT INTO {$this->table} 
                (channel, level, level_value, message, context, extra, remote_addr, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $context = !empty($record['context']) ? json_encode($record['context']) : null;
        $extra = !empty($record['extra']) ? json_encode($record['extra']) : null;

        // Extract IP and UA from extra if available, otherwise null
        $ip = $record['extra']['ip'] ?? null;
        $ua = $record['extra']['user_agent'] ?? null;

        $this->db->query($sql, [
            $record['channel'],
            $record['level'],
            $record['level_value'],
            $record['message'],
            $context,
            $extra,
            $ip,
            $ua,
            $record['datetime']->format('Y-m-d H:i:s')
        ]);
    }
}
