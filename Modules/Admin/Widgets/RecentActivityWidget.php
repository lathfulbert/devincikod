<?php

namespace Modules\Admin\Widgets;

use App\Core\Widget\AbstractWidget;
use App\Core\Database\Database;

/**
 * Widget RecentActivity
 *
 * Affiche l'activité récente du système
 *
 * Type: Activité / Liste
 * Permission: admin.access
 */
class RecentActivityWidget extends AbstractWidget
{
    protected string $name = 'admin.recent_activity';
    protected string $type = 'activity';
    protected ?string $permission = 'admin.access';
    protected bool $cacheable = true;
    protected int $cacheDuration = 60; // 1 minute

    protected array $config = [
        'title' => 'Activité récente',
        'description' => 'Dernières actions dans le système',
        'icon' => 'clock',
        'color' => 'info',
        'order' => 5
    ];

    protected function calculateData(): array
    {
        $activities = [];

        // Récupérer les derniers utilisateurs créés
        $recentUsers = $this->getRecentUsers();
        $activities = array_merge($activities, $recentUsers);

        // Récupérer les derniers SMS envoyés
        $recentSms = $this->getRecentSms();
        $activities = array_merge($activities, $recentSms);

        // Trier par date décroissante
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        // Limiter à 10 activités
        $activities = array_slice($activities, 0, 10);

        return [
            'title' => 'Activité récente',
            'value' => count($activities) . ' événements récents',
            'description' => 'Dernières actions dans le système',
            'icon' => 'clock',
            'trend' => [
                'percentage' => 0,
                'direction' => 'stable'
            ],
            'meta' => [
                'activities' => $activities,
                'count' => count($activities)
            ]
        ];
    }

    private function getRecentUsers(): array
    {
        try {
            $db = Database::getInstance();
            $result = $db->query("
                SELECT username, email, created_at
                FROM users
                ORDER BY created_at DESC
                LIMIT 5
            ")->fetchAll();

            $activities = [];
            foreach ($result as $user) {
                $activities[] = [
                    'title' => "Nouvel utilisateur: {$user['username']}",
                    'time' => $this->timeAgo($user['created_at']),
                    'timestamp' => $user['created_at'],
                    'icon' => 'user-plus',
                    'type' => 'user'
                ];
            }

            return $activities;
        } catch (\Exception $e) {
            error_log("Error fetching recent users: " . $e->getMessage());
            return [];
        }
    }

    private function getRecentSms(): array
    {
        try {
            $db = Database::getInstance();
            $result = $db->query("
                SELECT recipient, status, created_at
                FROM sms_messages
                ORDER BY created_at DESC
                LIMIT 5
            ")->fetchAll();

            $activities = [];
            foreach ($result as $sms) {
                $activities[] = [
                    'title' => "SMS envoyé à {$sms['recipient']}",
                    'time' => $this->timeAgo($sms['created_at']),
                    'timestamp' => $sms['created_at'],
                    'icon' => 'send',
                    'type' => 'sms'
                ];
            }

            return $activities;
        } catch (\Exception $e) {
            error_log("Error fetching recent SMS: " . $e->getMessage());
            return [];
        }
    }

    private function timeAgo(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'À l\'instant';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return "Il y a {$minutes} minute" . ($minutes > 1 ? 's' : '');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "Il y a {$hours} heure" . ($hours > 1 ? 's' : '');
        } else {
            $days = floor($diff / 86400);
            return "Il y a {$days} jour" . ($days > 1 ? 's' : '');
        }
    }
}
