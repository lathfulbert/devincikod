<?php

namespace Modules\Backup\Database\Seeders;

use App\Core\Database\Seeder;
use Modules\Notifications\Models\NotificationTemplate;

class BackupNotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Backup Successful Template - Email
        if (!NotificationTemplate::where('name', 'backup_success')->where('channel', 'email')->first()) {
            NotificationTemplate::create([
                'name' => 'backup_success_email',
                'channel' => 'email',
                'version' => 1,
                'subject' => 'Backup Successful: {{ filename }}',
                'body_html' => '<h1>Backup Successful</h1><p>Your backup <strong>{{ filename }}</strong> ({{ size }}) was created successfully on {{ date }}.</p>',
                'body_text' => "Backup Successful\n\nYour backup {{ filename }} ({{ size }}) was created successfully on {{ date }}.",
                'body_sms' => null,
                'push_title' => null,
                'push_body' => null,
                'variables' => json_encode(['filename', 'size', 'date', 'backup_id']),
                'active' => true,
            ]);
        }

        // Backup Successful Template - Database
        if (!NotificationTemplate::where('name', 'backup_success')->where('channel', 'database')->first()) {
            NotificationTemplate::create([
                'name' => 'backup_success_database',
                'channel' => 'database',
                'version' => 1,
                'subject' => 'Backup Successful',
                'body_html' => 'Backup {{ filename }} created successfully.',
                'body_text' => 'Backup {{ filename }} created successfully.',
                'body_sms' => null,
                'push_title' => null,
                'push_body' => null,
                'variables' => json_encode(['filename', 'size', 'date', 'backup_id']),
                'active' => true,
            ]);
        }

        // Backup Failed Template - Email
        if (!NotificationTemplate::where('name', 'backup_failed')->where('channel', 'email')->first()) {
            NotificationTemplate::create([
                'name' => 'backup_failed_email',
                'channel' => 'email',
                'version' => 1,
                'subject' => 'Backup Failed',
                'body_html' => '<h1>Backup Failed</h1><p>The backup process failed with the following error:</p><pre>{{ error }}</pre><p>Date: {{ date }}</p>',
                'body_text' => "Backup Failed\n\nThe backup process failed with the following error:\n{{ error }}\nDate: {{ date }}",
                'body_sms' => null,
                'push_title' => null,
                'push_body' => null,
                'variables' => json_encode(['error', 'date', 'backup_id']),
                'active' => true,
            ]);
        }

        // Backup Failed Template - Database
        if (!NotificationTemplate::where('name', 'backup_failed')->where('channel', 'database')->first()) {
            NotificationTemplate::create([
                'name' => 'backup_failed_database',
                'channel' => 'database',
                'version' => 1,
                'subject' => 'Backup Failed',
                'body_html' => 'Backup failed: {{ error }}',
                'body_text' => 'Backup failed: {{ error }}',
                'body_sms' => null,
                'push_title' => null,
                'push_body' => null,
                'variables' => json_encode(['error', 'date', 'backup_id']),
                'active' => true,
            ]);
        }
    }
}
