<?php
namespace Modules\Dashboard\Widgets;

use App\Core\Application;
use App\Core\Widget\AbstractWidget;

class SmsStatsWidget extends AbstractWidget
{
    protected string $name = 'dashboard.sms_stats';
    protected string $type = 'stat';
    protected ?string $permission = 'access.dashboard';
    protected bool $cacheable = false;
    protected array $config = [
        'title' => 'Statistiques SMS',
        'icon' => 'bar-chart-2',
        'color' => 'primary',
        'order' => 2
    ];

    protected function calculateData(): array
    {
        $db = \App\Core\Database\Database::getInstance();
        $sent = $db->query("SELECT COUNT(*) as total FROM sms_messages WHERE status = 'sent'")->fetch()['total'] ?? 0;
        $failed = $db->query("SELECT COUNT(*) as total FROM sms_messages WHERE status = 'failed'")->fetch()['total'] ?? 0;
        $pending = $db->query("SELECT COUNT(*) as total FROM sms_campaigns WHERE status = 'pending'")->fetch()['total'] ?? 0;
        return [
            'title' => 'Statistiques SMS',
            'icon' => 'bar-chart-2',
            'color' => 'primary',
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending
        ];
    }

    public function render(): string
    {
        $data = $this->getData();
        ob_start();
        ?>
        <div class="card text-bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title"><i data-feather="bar-chart-2"></i> Statistiques SMS</h5>
                <ul class="list-unstyled mb-0">
                    <li><strong>Envoyés :</strong> <?= $data['sent'] ?></li>
                    <li><strong>Échoués :</strong> <?= $data['failed'] ?></li>
                    <li><strong>Campagnes en cours :</strong> <?= $data['pending'] ?></li>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
