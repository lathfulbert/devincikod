<?php

namespace Modules\SmsCore\Models;

use App\Core\Database\Model;

class SenderName extends Model
{
    protected static string $table = 'sender_names';

    protected array $fillable = [
        'name',
        'operator',
        'status',
        'is_active',
        'validation_date',
        'notes',
        'created_by'
    ];

    /**
     * Get all active and approved sender names
     */
    public static function getActiveApproved(): array
    {
        return self::where('status', 'approved')
            ->where('is_active', 1)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get sender names assigned to a specific user
     */
    public static function getForUser(int $userId): array
    {
        $db = \App\Core\Database\Database::getInstance();

        return $db->query("
            SELECT sn.*
            FROM sender_names sn
            INNER JOIN user_sender_names usn ON sn.id = usn.sender_name_id
            WHERE usn.user_id = ?
              AND sn.status = 'approved'
              AND sn.is_active = 1
            ORDER BY sn.name ASC
        ", [$userId])->fetchAll(\PDO::FETCH_CLASS, self::class);
    }

    /**
     * Check if a user has access to a sender name
     */
    public static function userHasAccess(int $userId, int $senderNameId): bool
    {
        $db = \App\Core\Database\Database::getInstance();

        $result = $db->query("
            SELECT COUNT(*) as count
            FROM user_sender_names
            WHERE user_id = ? AND sender_name_id = ?
        ", [$userId, $senderNameId])->fetch();

        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Assign sender name to a user
     */
    public static function assignToUser(int $senderNameId, int $userId, int $assignedBy): bool
    {
        $db = \App\Core\Database\Database::getInstance();

        try {
            $db->query("
                INSERT INTO user_sender_names (user_id, sender_name_id, assigned_by, assigned_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE assigned_by = ?, assigned_at = NOW()
            ", [$userId, $senderNameId, $assignedBy, $assignedBy]);

            return true;
        } catch (\Exception $e) {
            error_log("Failed to assign sender name: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove sender name from a user
     */
    public static function removeFromUser(int $senderNameId, int $userId): bool
    {
        $db = \App\Core\Database\Database::getInstance();

        try {
            $db->query("
                DELETE FROM user_sender_names
                WHERE user_id = ? AND sender_name_id = ?
            ", [$userId, $senderNameId]);

            return true;
        } catch (\Exception $e) {
            error_log("Failed to remove sender name: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get users assigned to this sender name
     */
    public function getAssignedUsers(): array
    {
        $db = \App\Core\Database\Database::getInstance();

        return $db->query("
            SELECT u.id, u.username, u.email, u.first_name, u.last_name, usn.assigned_at
            FROM users u
            INNER JOIN user_sender_names usn ON u.id = usn.user_id
            WHERE usn.sender_name_id = ?
            ORDER BY u.username ASC
        ", [$this->id])->fetchAll();
    }

    /**
     * Sync sender names for a user (replace all assignments)
     */
    public static function syncForUser(int $userId, array $senderNameIds, int $assignedBy): bool
    {
        $db = \App\Core\Database\Database::getInstance();

        try {
            // Start transaction
            $db->beginTransaction();

            // Remove all existing assignments
            $db->query("DELETE FROM user_sender_names WHERE user_id = ?", [$userId]);

            // Add new assignments
            if (!empty($senderNameIds)) {
                $values = [];
                $params = [];

                foreach ($senderNameIds as $senderNameId) {
                    $values[] = "(?, ?, ?, NOW())";
                    $params[] = $userId;
                    $params[] = $senderNameId;
                    $params[] = $assignedBy;
                }

                $sql = "INSERT INTO user_sender_names (user_id, sender_name_id, assigned_by, assigned_at)
                        VALUES " . implode(", ", $values);

                $db->query($sql, $params);
            }

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollback();
            error_log("Failed to sync sender names: " . $e->getMessage());
            return false;
        }
    }
}
