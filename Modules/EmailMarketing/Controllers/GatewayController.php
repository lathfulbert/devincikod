<?php
namespace Modules\EmailMarketing\Controllers;

use App\Core\Application;
use Modules\EmailMarketing\Services\EmailGatewayFactory;

class GatewayController
{
    protected EmailGatewayFactory $factory;

    public function __construct()
    {
        $this->factory = new EmailGatewayFactory();
    }

    public function index()
    {
        $gateways = [];
        $defaultGateway = $this->factory->getDefaultGatewayPublic();
        foreach ($this->factory->getAvailableGateways() as $name) {
            $config = $this->factory->getGatewayConfigPublic($name);
            $gateways[] = [
                'name' => $name,
                'type' => $config['driver'] ?? $name,
                'active' => ($name === $defaultGateway),
            ];
        }
        return view('EmailMarketing/gateways/index', ['gateways' => $gateways]);
    }

    public function create()
    {
        return view('EmailMarketing/gateways/form', ['edit' => false, 'gateway' => []]);
    }

    public function store()
    {
        $data = $_POST;
        $name = $data['gateway_name'];
        $type = $data['gateway_type'];
        $config = [
            'driver' => $type,
            'from_email' => $data['from_email'],
            'from_name' => $data['from_name'],
            'api_key' => $data['api_key'] ?? '',
            'host' => $data['host'] ?? '',
            'port' => $data['port'] ?? '',
            'username' => $data['username'] ?? '',
            'password' => $data['password'] ?? '',
            'encryption' => $data['encryption'] ?? '',
        ];
        // TODO: Save config in app config (file/db)
        $_SESSION['flash_success'] = 'Gateway ajouté.';
        redirect(route('admin.email-marketing.gateways.index'));
    }

    public function edit($name)
    {
        $config = $this->factory->getGatewayConfigPublic($name);
        return view('EmailMarketing/gateways/form', ['edit' => true, 'gateway' => array_merge(['name' => $name], $config)]);
    }

    public function update($name)
    {
        $data = $_POST;
        // TODO: Update config in app config (file/db)
        $_SESSION['flash_success'] = 'Gateway modifié.';
        redirect(route('admin.email-marketing.gateways.index'));
    }

    public function delete($name)
    {
        // TODO: Remove config from app config (file/db)
        $_SESSION['flash_success'] = 'Gateway supprimé.';
        redirect(route('admin.email-marketing.gateways.index'));
    }
}
