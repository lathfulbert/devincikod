<?php

namespace App\Core\Notifications;

use Modules\Notifications\Models\UserNotificationPreference;

/**
 * Notification Router
 * 
 * Routes notifications to appropriate channels based on user preferences
 */
class NotificationRouter
{
    /**
     * Get enabled channels for user
     */
    public function getChannelsForUser(int $userId, array $requestedChannels = []): array
    {
        $preferences = UserNotificationPreference::forUser($userId);

        // If user is in DND, skip all channels
        if ($preferences->isInDND()) {
            return [];
        }

        // If no channels requested, use all enabled channels
        if (empty($requestedChannels)) {
            $requestedChannels = $preferences->channels_enabled ?? ['email'];
        }

        // Filter channels based on preferences
        $enabledChannels = [];

        foreach ($requestedChannels as $channel) {
            // Check if channel is enabled and user has opted in
            if ($preferences->isChannelEnabled($channel) && $preferences->hasOptedIn($channel)) {
                $enabledChannels[] = $channel;
            }
        }

        return $enabledChannels;
    }

    /**
     * Check if user can receive notification on channel
     */
    public function canSendToUser(int $userId, string $channel): bool
    {
        $channels = $this->getChannelsForUser($userId, [$channel]);
        return in_array($channel, $channels);
    }

    /**
     * Get priority channel for user
     */
    public function getPriorityChannel(int $userId): ?string
    {
        $channels = $this->getChannelsForUser($userId);

        // Priority order: push > email > sms
        $priority = ['push', 'email', 'sms'];

        foreach ($priority as $channel) {
            if (in_array($channel, $channels)) {
                return $channel;
            }
        }

        return null;
    }
}
