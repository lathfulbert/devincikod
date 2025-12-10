@extends('backend.layouts.master')

@section('title', 'Documentation API')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'API Keys', 'url' => '/admin/api-keys'],
        ['label' => 'Documentation']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <?php
            component('card-start', ['card_title' => "Documentation de l'API"]);
            ?>

            <div class="alert alert-info">
                <i data-feather="info"></i>
                <strong>Base URL:</strong> <code><?= url('/api') ?></code>
            </div>

            <h4 class="mt-4">Authentification</h4>
            <p>Toutes les requêtes API doivent inclure votre clé API dans l'en-tête de la requête :</p>
            <pre class="bg-light p-3 rounded"><code>Authorization: Bearer VOTRE_CLE_API</code></pre>

            <h4 class="mt-4">Endpoints Disponibles</h4>

            <!-- Endpoint: Informations Utilisateur -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <span class="badge bg-success">GET</span>
                    <strong>/api/user</strong>
                </div>
                <div class="card-body">
                    <p>Récupère les informations de l'utilisateur authentifié.</p>
                    <h6>Exemple de réponse :</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "data": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "created_at": "2024-01-01 00:00:00"
  }
}</code></pre>
                </div>
            </div>

            <!-- Endpoint: Envoyer SMS -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <span class="badge bg-warning">POST</span>
                    <strong>/api/sms/send</strong>
                </div>
                <div class="card-body">
                    <p>Envoie un SMS à un numéro de téléphone.</p>
                    <h6>Paramètres requis :</h6>
                    <ul>
                        <li><code>to</code> - Numéro de téléphone du destinataire</li>
                        <li><code>message</code> - Contenu du message (max 160 caractères)</li>
                    </ul>
                    <h6>Exemple de requête :</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "to": "+221771234567",
  "message": "Votre code de vérification est: 123456"
}</code></pre>
                    <h6>Exemple de réponse :</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "message": "SMS sent successfully",
  "data": {
    "id": "sms_abc123",
    "status": "sent",
    "cost": 25
  }
}</code></pre>
                </div>
            </div>

            <!-- Endpoint: Historique SMS -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <span class="badge bg-success">GET</span>
                    <strong>/api/sms/history</strong>
                </div>
                <div class="card-body">
                    <p>Récupère l'historique des SMS envoyés.</p>
                    <h6>Paramètres optionnels :</h6>
                    <ul>
                        <li><code>page</code> - Numéro de la page (défaut: 1)</li>
                        <li><code>limit</code> - Nombre de résultats par page (défaut: 20)</li>
                    </ul>
                    <h6>Exemple de réponse :</h6>
                    <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "data": [
    {
      "id": "sms_abc123",
      "to": "+221771234567",
      "message": "Hello World",
      "status": "delivered",
      "sent_at": "2024-01-15 10:30:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total": 95
  }
}</code></pre>
                </div>
            </div>

            <h4 class="mt-4">Codes d'Erreur</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>400</code></td>
                        <td>Requête invalide (paramètres manquants ou incorrects)</td>
                    </tr>
                    <tr>
                        <td><code>401</code></td>
                        <td>Non autorisé (clé API invalide ou manquante)</td>
                    </tr>
                    <tr>
                        <td><code>403</code></td>
                        <td>Accès interdit (permissions insuffisantes)</td>
                    </tr>
                    <tr>
                        <td><code>404</code></td>
                        <td>Ressource non trouvée</td>
                    </tr>
                    <tr>
                        <td><code>429</code></td>
                        <td>Trop de requêtes (limite de débit dépassée)</td>
                    </tr>
                    <tr>
                        <td><code>500</code></td>
                        <td>Erreur serveur interne</td>
                    </tr>
                </tbody>
            </table>

            <h4 class="mt-4">Exemple d'Utilisation (cURL)</h4>
            <pre class="bg-dark text-white p-3 rounded"><code>curl -X POST <?= url('/api/sms/send') ?> \
  -H "Authorization: Bearer VOTRE_CLE_API" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+221771234567",
    "message": "Test message"
  }'</code></pre>

            <h4 class="mt-4">Limites de Débit</h4>
            <ul>
                <li>Limite par défaut : <strong>100 requêtes par minute</strong></li>
                <li>Limite pour l'envoi SMS : <strong>10 requêtes par minute</strong></li>
            </ul>

            <div class="alert alert-warning mt-4">
                <i data-feather="alert-triangle"></i>
                <strong>Important :</strong> Ne partagez jamais votre clé API publiquement. Utilisez toujours HTTPS pour les requêtes.
            </div>

            <?php component('card-end'); ?>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
feather.replace();
</script>
@endsection
