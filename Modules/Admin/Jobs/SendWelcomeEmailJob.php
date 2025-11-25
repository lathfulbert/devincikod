<?php

namespace Modules\Admin\Jobs;

use App\Core\Queue\Job;

/**
 * SendWelcomeEmailJob
 * 
 * Example job that sends a welcome email to a user.
 */
class SendWelcomeEmailJob extends Job
{
    protected int $tries = 3;
    protected int $timeout = 30;

    public function handle(): void
    {
        $data = $this->getData();

        $email = $data['email'] ?? 'unknown';
        $name = $data['name'] ?? 'User';

        // Simulate email sending
        echo "📧 Sending welcome email...\n";
        echo "   To: $email\n";
        echo "   Name: $name\n";

        // In a real implementation, you would use a mail library here
        // mail($email, "Welcome to our platform!", "Hello $name...");

        sleep(1); // Simulate processing time

        echo "✅ Welcome email sent successfully!\n";
    }

    public function failed(\Throwable $exception): void
    {
        $data = $this->getData();
        $email = $data['email'] ?? 'unknown';

        error_log("Failed to send welcome email to $email: " . $exception->getMessage());
        echo "❌ Failed to send welcome email: " . $exception->getMessage() . "\n";
    }
}
