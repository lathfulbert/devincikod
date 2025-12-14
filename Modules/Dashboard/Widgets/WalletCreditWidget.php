<?php
namespace Modules\Dashboard\Widgets;

use App\Core\Application;
use App\Core\Widget\AbstractWidget;

class WalletCreditWidget extends AbstractWidget
{
    protected string $name = 'dashboard.wallet_credit';
    protected string $type = 'stat';
    protected ?string $permission = 'access.dashboard';
    protected bool $cacheable = false;
    protected array $config = [
        'title' => 'Crédit Wallet',
        'icon' => 'dollar-sign',
        'color' => 'success',
        'order' => 3
    ];

    protected function calculateData(): array
    {
        $db = \App\Core\Database\Database::getInstance();
        $credit = $db->query("SELECT SUM(balance) as total FROM wallets WHERE status = 'active'")->fetch()['total'] ?? 0;
        return [
            'title' => 'Crédit Wallet',
            'icon' => 'dollar-sign',
            'color' => 'success',
            'credit' => $credit
        ];
    }

    public function render(): string
    {
        $data = $this->getData();
        ob_start();
        ?>
        <div class="card text-bg-success h-100">
            <div class="card-body">
                <h5 class="card-title"><i data-feather="dollar-sign"></i> Crédit Wallet</h5>
                <p class="display-6 fw-bold mb-0"><?= number_format($data['credit'], 0, ',', ' ') ?> F&nbsp;CFA</p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
