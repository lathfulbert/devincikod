<?php

namespace Modules\Wallet\Widgets;

use App\Core\Widget\AbstractWidget;
use Modules\Wallet\Models\Wallet;
use Modules\Users\Models\User;

/**
 * Widget WalletCredit
 * Affiche le crédit total des wallets (tous ou par owner)
 */
class WalletCreditWidget extends AbstractWidget
{
    protected string $name = 'wallets.credit';
    protected string $type = 'stat';
    protected ?string $permission = null;
    protected bool $cacheable = false;
    protected int $cacheDuration = 300;

    protected array $config = [
        'title' => 'Crédit Wallet',
        'description' => 'Crédit total de vos wallets ou tous (admin)',
        'icon' => 'dollar-sign',
        'color' => 'success',
        'order' => 4
    ];

    protected function calculateData(): array
    {
        $user = null;
        if (isset($_SESSION['user']['id'])) {
            $user = User::find($_SESSION['user']['id']);
        }
        if (!$user) {
            return [
                'title' => $this->config['title'],
                'value' => 0,
                'description' => 'Non connecté',
                'icon' => $this->config['icon'],
                'trend' => null,
                'meta' => []
            ];
        }


        // Si admin ou dashboard_view_all, voir tous les crédits
        if ($user->hasRole('admin') || $user->can('dashboard_view_all')) {
            $wallets = Wallet::all();
            $total = 0;
            foreach ($wallets as $wallet) {
                $total += (float)($wallet->balance ?? 0);
            }
            $desc = 'Crédit total de tous les wallets';
        } else {
            $wallets = Wallet::where('created_by', $user->id)->get();
            $total = 0;
            foreach ($wallets as $wallet) {
                $total += (float)($wallet->balance ?? 0);
            }
            $desc = 'Crédit de vos wallets';
        }

        return [
            'title' => $this->config['title'],
            'value' => number_format($total, 0, ',', ' '),
            'description' => $desc,
            'icon' => $this->config['icon'],
            'trend' => null,
            'meta' => [
                'created_by' => $user->id,
                'is_admin' => $user->hasRole('admin'),
            ]
        ];
    }

    public function canView($user): bool
    {
        return $user !== null;
    }
}
