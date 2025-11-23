<?php

namespace Modules\Settings\Controllers;

use App\Core\Application;
use Modules\Settings\Models\Webhook;

class WebhookController
{
    public function index()
    {
        $app = Application::getInstance();
        $webhooks = Webhook::orderBy('created_at', 'DESC')->get();

        echo $app->view->render('settings/webhooks/index', [
            'title' => 'Gestion des Webhooks',
            'webhooks' => $webhooks,
        ]);
    }

    public function create()
    {
        $app = Application::getInstance();

        $events = [
            'user.created' => 'Utilisateur créé',
            'user.updated' => 'Utilisateur modifié',
            'user.deleted' => 'Utilisateur supprimé',
            'order.created' => 'Commande créée',
            'order.completed' => 'Commande complétée',
            'payment.success' => 'Paiement réussi',
        ];

        echo $app->view->render('settings/webhooks/create', [
            'title' => 'Nouveau Webhook',
            'events' => $events,
        ]);
    }

    public function store()
    {
        $app = Application::getInstance();
        $request = $app->request;

        $data = [
            'name' => $request->post('name'),
            'url' => $request->post('url'),
            'events' => json_encode($request->post('events', [])),
            'secret' => $request->post('secret', ''),
            'is_active' => $request->post('is_active', '1'),
            'headers' => json_encode($request->post('headers', [])),
            'retry_count' => $request->post('retry_count', '3'),
            'timeout' => $request->post('timeout', '30'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        Webhook::create($data);

        $app->session->setFlash('success', 'Webhook créé avec succès.');
        return $app->redirect('/admin/settings/webhooks');
    }

    public function edit($params)
    {
        $app = Application::getInstance();
        $id = $params['id'] ?? null;

        $webhook = Webhook::find($id);

        if (!$webhook) {
            $app->session->setFlash('error', 'Webhook introuvable.');
            return $app->redirect('/admin/settings/webhooks');
        }

        $events = [
            'user.created' => 'Utilisateur créé',
            'user.updated' => 'Utilisateur modifié',
            'user.deleted' => 'Utilisateur supprimé',
            'order.created' => 'Commande créée',
            'order.completed' => 'Commande complétée',
            'payment.success' => 'Paiement réussi',
        ];

        echo $app->view->render('settings/webhooks/edit', [
            'title' => 'Modifier le Webhook',
            'webhook' => $webhook,
            'events' => $events,
        ]);
    }

    public function update($params)
    {
        $app = Application::getInstance();
        $request = $app->request;
        $id = $params['id'] ?? null;

        Webhook::where('id', $id)->update([
            'name' => $request->post('name'),
            'url' => $request->post('url'),
            'events' => json_encode($request->post('events', [])),
            'secret' => $request->post('secret', ''),
            'is_active' => $request->post('is_active', '0'),
            'headers' => json_encode($request->post('headers', [])),
            'retry_count' => $request->post('retry_count', '3'),
            'timeout' => $request->post('timeout', '30'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $app->session->setFlash('success', 'Webhook mis à jour avec succès.');
        return $app->redirect('/admin/settings/webhooks');
    }

    public function delete($params)
    {
        $app = Application::getInstance();
        $id = $params['id'] ?? null;

        Webhook::where('id', $id)->delete();

        $app->session->setFlash('success', 'Webhook supprimé avec succès.');
        return $app->redirect('/admin/settings/webhooks');
    }

    public function test($params)
    {
        $app = Application::getInstance();
        $id = $params['id'] ?? null;

        $webhook = Webhook::find($id);

        if (!$webhook) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Webhook introuvable']);
            exit;
        }

        $result = $webhook->test();

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function logs($params)
    {
        $app = Application::getInstance();
        $id = $params['id'] ?? null;

        $webhook = Webhook::find($id);

        if (!$webhook) {
            $app->session->setFlash('error', 'Webhook introuvable.');
            return $app->redirect('/admin/settings/webhooks');
        }

        $logs = $webhook->logs();

        echo $app->view->render('settings/webhooks/logs', [
            'title' => 'Logs du Webhook',
            'webhook' => $webhook,
            'logs' => $logs,
        ]);
    }
}
