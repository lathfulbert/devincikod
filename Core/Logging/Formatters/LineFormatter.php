<?php

namespace App\Core\Logging\Formatters;

use DateTime;

class LineFormatter
{
    protected string $format;
    protected string $dateFormat;

    public function __construct(
        ?string $format = null,
        ?string $dateFormat = null
    ) {
        $this->format = $format ?: "[%datetime%] %channel%.%level%: %message% %context% %extra%\n";
        $this->dateFormat = $dateFormat ?: 'Y-m-d H:i:s';
    }

    public function format(array $record): string
    {
        $output = $this->format;

        $vars = [
            '%datetime%' => $this->formatDate($record['datetime']),
            '%channel%' => $record['channel'],
            '%level%' => strtoupper($record['level']),
            '%message%' => $record['message'],
            '%context%' => $this->formatContext($record['context']),
            '%extra%' => $this->formatExtra($record['extra']),
        ];

        foreach ($vars as $var => $val) {
            $output = str_replace($var, $val, $output);
        }

        return $output;
    }

    protected function formatDate(DateTime $date): string
    {
        return $date->format($this->dateFormat);
    }

    protected function formatContext(array $context): string
    {
        if (empty($context)) {
            return '';
        }

        return json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function formatExtra(array $extra): string
    {
        if (empty($extra)) {
            return '';
        }

        $filtered = array_filter($extra, fn($v) => $v !== null);

        if (empty($filtered)) {
            return '';
        }

        return json_encode($filtered, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
