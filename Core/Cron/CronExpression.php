<?php

namespace App\Core\Cron;

/**
 * Class CronExpression
 * 
 * Parses and validates cron expressions.
 * Determines if a task is due to run based on the expression.
 */
class CronExpression
{
    /**
     * Determine if the cron expression is due to run.
     *
     * @param string $expression Cron expression (e.g., "* * * * *")
     * @param string $timezone Timezone to check against
     * @return bool
     */
    public static function isDue(string $expression, string $timezone = 'UTC'): bool
    {
        $date = new \DateTime('now', new \DateTimeZone($timezone));

        // Split expression into parts
        // Format: minute hour day month day_of_week
        $parts = preg_split('/\s+/', trim($expression));

        if (count($parts) !== 5) {
            return false; // Invalid expression
        }

        list($minute, $hour, $day, $month, $weekday) = $parts;

        return self::partMatches($minute, (int)$date->format('i')) &&
            self::partMatches($hour, (int)$date->format('H')) &&
            self::partMatches($day, (int)$date->format('d')) &&
            self::partMatches($month, (int)$date->format('m')) &&
            self::partMatches($weekday, (int)$date->format('w'));
    }

    /**
     * Check if a specific part of the expression matches the current value.
     */
    private static function partMatches(string $part, int $currentValue): bool
    {
        // Handle wildcard (*)
        if ($part === '*') {
            return true;
        }

        // Handle lists (1,2,3)
        if (str_contains($part, ',')) {
            $values = explode(',', $part);
            foreach ($values as $value) {
                if (self::partMatches($value, $currentValue)) {
                    return true;
                }
            }
            return false;
        }

        // Handle ranges (1-5)
        if (str_contains($part, '-')) {
            list($min, $max) = explode('-', $part);
            return $currentValue >= (int)$min && $currentValue <= (int)$max;
        }

        // Handle steps (*/5)
        if (str_contains($part, '/')) {
            list($base, $step) = explode('/', $part);
            if ($base === '*') {
                return $currentValue % (int)$step === 0;
            }
            // Complex steps like 5-20/5 not fully supported in this simple parser
            // but standard */5 is supported
        }

        // Handle exact match
        return (int)$part === $currentValue;
    }
}
