-- Notification Templates pour Wallet Topup Requests
-- À exécuter après la création de la table notification_templates

-- Template : Demande de rechargement soumise (confirmation utilisateur)
INSERT INTO `notification_templates`
(`name`, `channel`, `version`, `subject`, `body_html`, `body_text`, `variables`, `active`)
VALUES
(
    'wallet_topup_request_submitted',
    'email',
    1,
    'Demande de rechargement wallet soumise',
    '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 30px; }
        .info-box { background-color: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; }
        .amount { font-size: 24px; font-weight: bold; color: #4CAF50; }
        .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }
        .button { background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; display: inline-block; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Demande de Rechargement Soumise</h1>
        </div>
        <div class="content">
            <p>Bonjour <strong>{{user_name}}</strong>,</p>

            <p>Votre demande de rechargement wallet a bien été soumise avec succès.</p>

            <div class="info-box">
                <p><strong>Détails de la demande :</strong></p>
                <ul>
                    <li><strong>N° de demande :</strong> #{{request_id}}</li>
                    <li><strong>Montant :</strong> <span class="amount">{{amount}} XOF</span></li>
                    <li><strong>Méthode de paiement :</strong> {{payment_method}}</li>
                    <li><strong>Statut :</strong> En attente de validation</li>
                    <li><strong>Date :</strong> {{created_at}}</li>
                </ul>
            </div>

            <p><strong>Prochaines étapes :</strong></p>
            <p>Votre demande est actuellement en attente de validation par notre équipe. Vous recevrez une notification par email dès que votre demande sera traitée.</p>

            <p>Pour les paiements offline (Mobile Money, Cash, Virement), assurez-vous d''avoir effectué le paiement et conservé votre preuve de transaction.</p>

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{view_url}}" class="button">Voir ma demande</a>
            </p>
        </div>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; {{year}} {{site_name}}. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>',
    'Bonjour {{user_name}},

Votre demande de rechargement wallet a bien été soumise avec succès.

Détails de la demande :
- N° de demande : #{{request_id}}
- Montant : {{amount}} XOF
- Méthode de paiement : {{payment_method}}
- Statut : En attente de validation
- Date : {{created_at}}

Prochaines étapes :
Votre demande est actuellement en attente de validation par notre équipe. Vous recevrez une notification par email dès que votre demande sera traitée.

Pour les paiements offline (Mobile Money, Cash, Virement), assurez-vous d''avoir effectué le paiement et conservé votre preuve de transaction.

Voir ma demande : {{view_url}}

Cet email a été envoyé automatiquement, merci de ne pas y répondre.
© {{year}} {{site_name}}. Tous droits réservés.',
    '["user_name", "request_id", "amount", "payment_method", "created_at", "view_url", "site_name", "year"]',
    1
);

-- Template : Demande de rechargement approuvée
INSERT INTO `notification_templates`
(`name`, `channel`, `version`, `subject`, `body_html`, `body_text`, `variables`, `active`)
VALUES
(
    'wallet_topup_request_approved',
    'email',
    1,
    'Demande de rechargement approuvée - Wallet crédité',
    '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 30px; }
        .success-box { background-color: #e8f5e9; border-left: 4px solid #4CAF50; padding: 20px; margin: 20px 0; text-align: center; }
        .amount { font-size: 32px; font-weight: bold; color: #4CAF50; }
        .info-box { background-color: #fff; border: 1px solid #ddd; padding: 15px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }
        .button { background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; display: inline-block; border-radius: 5px; }
        .check-icon { font-size: 48px; color: #4CAF50; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Demande Approuvée</h1>
        </div>
        <div class="content">
            <p>Bonjour <strong>{{user_name}}</strong>,</p>

            <p>Excellente nouvelle ! Votre demande de rechargement wallet a été approuvée.</p>

            <div class="success-box">
                <div class="check-icon">✓</div>
                <h2>Wallet Crédité</h2>
                <p class="amount">+ {{amount}} XOF</p>
                <p>Votre nouveau solde : <strong>{{new_balance}} XOF</strong></p>
            </div>

            <div class="info-box">
                <p><strong>Détails de la demande :</strong></p>
                <ul>
                    <li><strong>N° de demande :</strong> #{{request_id}}</li>
                    <li><strong>Montant crédité :</strong> {{amount}} XOF</li>
                    <li><strong>Méthode de paiement :</strong> {{payment_method}}</li>
                    <li><strong>Date de soumission :</strong> {{created_at}}</li>
                    <li><strong>Date d''approbation :</strong> {{approved_at}}</li>
                    <li><strong>Approuvé par :</strong> {{reviewed_by}}</li>
                </ul>

                {{#admin_notes}}
                <p><strong>Notes de l''administrateur :</strong></p>
                <p style="background-color: #f5f5f5; padding: 10px; border-radius: 5px;">{{admin_notes}}</p>
                {{/admin_notes}}
            </div>

            <p>Vous pouvez maintenant utiliser votre solde pour envoyer des SMS, effectuer des transactions, ou utiliser nos autres services.</p>

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{wallet_url}}" class="button">Voir mon wallet</a>
            </p>
        </div>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; {{year}} {{site_name}}. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>',
    'Bonjour {{user_name}},

Excellente nouvelle ! Votre demande de rechargement wallet a été approuvée.

✓ WALLET CRÉDITÉ
+ {{amount}} XOF
Votre nouveau solde : {{new_balance}} XOF

Détails de la demande :
- N° de demande : #{{request_id}}
- Montant crédité : {{amount}} XOF
- Méthode de paiement : {{payment_method}}
- Date de soumission : {{created_at}}
- Date d''approbation : {{approved_at}}
- Approuvé par : {{reviewed_by}}

{{#admin_notes}}
Notes de l''administrateur :
{{admin_notes}}
{{/admin_notes}}

Vous pouvez maintenant utiliser votre solde pour envoyer des SMS, effectuer des transactions, ou utiliser nos autres services.

Voir mon wallet : {{wallet_url}}

Cet email a été envoyé automatiquement, merci de ne pas y répondre.
© {{year}} {{site_name}}. Tous droits réservés.',
    '["user_name", "request_id", "amount", "new_balance", "payment_method", "created_at", "approved_at", "reviewed_by", "admin_notes", "wallet_url", "site_name", "year"]',
    1
);

-- Template : Demande de rechargement rejetée
INSERT INTO `notification_templates`
(`name`, `channel`, `version`, `subject`, `body_html`, `body_text`, `variables`, `active`)
VALUES
(
    'wallet_topup_request_rejected',
    'email',
    1,
    'Demande de rechargement rejetée',
    '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f44336; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 30px; }
        .warning-box { background-color: #fff3e0; border-left: 4px solid #ff9800; padding: 20px; margin: 20px 0; }
        .reason-box { background-color: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .info-box { background-color: #fff; border: 1px solid #ddd; padding: 15px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }
        .button { background-color: #2196F3; color: white; padding: 12px 30px; text-decoration: none; display: inline-block; border-radius: 5px; }
        .x-icon { font-size: 48px; color: #f44336; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Demande Rejetée</h1>
        </div>
        <div class="content">
            <p>Bonjour <strong>{{user_name}}</strong>,</p>

            <p>Nous regrettons de vous informer que votre demande de rechargement wallet #{{request_id}} n''a pas pu être approuvée.</p>

            <div class="reason-box">
                <p><strong>✗ Raison du rejet :</strong></p>
                <p>{{admin_notes}}</p>
            </div>

            <div class="info-box">
                <p><strong>Détails de la demande :</strong></p>
                <ul>
                    <li><strong>N° de demande :</strong> #{{request_id}}</li>
                    <li><strong>Montant :</strong> {{amount}} XOF</li>
                    <li><strong>Méthode de paiement :</strong> {{payment_method}}</li>
                    <li><strong>Date de soumission :</strong> {{created_at}}</li>
                    <li><strong>Date de rejet :</strong> {{rejected_at}}</li>
                    <li><strong>Rejeté par :</strong> {{reviewed_by}}</li>
                </ul>
            </div>

            <div class="warning-box">
                <p><strong>Que faire maintenant ?</strong></p>
                <ul>
                    <li>Vérifiez la raison du rejet ci-dessus</li>
                    <li>Assurez-vous que votre preuve de paiement est valide et lisible</li>
                    <li>Vérifiez que les informations de transaction sont correctes</li>
                    <li>Vous pouvez soumettre une nouvelle demande avec les informations corrigées</li>
                </ul>
            </div>

            <p>Si vous avez des questions ou si vous pensez qu''il s''agit d''une erreur, n''hésitez pas à contacter notre support.</p>

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{support_url}}" class="button">Contacter le Support</a>
                <a href="{{new_request_url}}" class="button" style="background-color: #4CAF50; margin-left: 10px;">Nouvelle Demande</a>
            </p>
        </div>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; {{year}} {{site_name}}. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>',
    'Bonjour {{user_name}},

Nous regrettons de vous informer que votre demande de rechargement wallet #{{request_id}} n''a pas pu être approuvée.

✗ RAISON DU REJET :
{{admin_notes}}

Détails de la demande :
- N° de demande : #{{request_id}}
- Montant : {{amount}} XOF
- Méthode de paiement : {{payment_method}}
- Date de soumission : {{created_at}}
- Date de rejet : {{rejected_at}}
- Rejeté par : {{reviewed_by}}

Que faire maintenant ?
- Vérifiez la raison du rejet ci-dessus
- Assurez-vous que votre preuve de paiement est valide et lisible
- Vérifiez que les informations de transaction sont correctes
- Vous pouvez soumettre une nouvelle demande avec les informations corrigées

Si vous avez des questions ou si vous pensez qu''il s''agit d''une erreur, n''hésitez pas à contacter notre support.

Contacter le Support : {{support_url}}
Nouvelle Demande : {{new_request_url}}

Cet email a été envoyé automatiquement, merci de ne pas y répondre.
© {{year}} {{site_name}}. Tous droits réservés.',
    '["user_name", "request_id", "amount", "payment_method", "created_at", "rejected_at", "reviewed_by", "admin_notes", "support_url", "new_request_url", "site_name", "year"]',
    1
);

-- Template : Notification admin - Nouvelle demande en attente
INSERT INTO `notification_templates`
(`name`, `channel`, `version`, `subject`, `body_html`, `body_text`, `variables`, `active`)
VALUES
(
    'wallet_topup_request_admin_notification',
    'email',
    1,
    '[ADMIN] Nouvelle demande de rechargement en attente',
    '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #ff9800; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 30px; }
        .alert-box { background-color: #fff3e0; border-left: 4px solid #ff9800; padding: 20px; margin: 20px 0; }
        .info-box { background-color: #fff; border: 1px solid #ddd; padding: 15px; margin: 20px 0; }
        .amount { font-size: 24px; font-weight: bold; color: #ff9800; }
        .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }
        .button { background-color: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; display: inline-block; border-radius: 5px; margin: 5px; }
        .button-reject { background-color: #f44336; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠ Nouvelle Demande en Attente</h1>
        </div>
        <div class="content">
            <p>Bonjour <strong>Admin</strong>,</p>

            <div class="alert-box">
                <p><strong>Une nouvelle demande de rechargement wallet nécessite votre validation.</strong></p>
            </div>

            <div class="info-box">
                <p><strong>Informations utilisateur :</strong></p>
                <ul>
                    <li><strong>Nom :</strong> {{user_name}}</li>
                    <li><strong>Email :</strong> {{user_email}}</li>
                    <li><strong>ID :</strong> {{user_id}}</li>
                </ul>

                <p><strong>Détails de la demande :</strong></p>
                <ul>
                    <li><strong>N° de demande :</strong> #{{request_id}}</li>
                    <li><strong>Montant :</strong> <span class="amount">{{amount}} XOF</span></li>
                    <li><strong>Méthode de paiement :</strong> {{payment_method}}</li>
                    <li><strong>Date de soumission :</strong> {{created_at}}</li>
                    <li><strong>IP utilisateur :</strong> {{ip_address}}</li>
                </ul>

                {{#user_notes}}
                <p><strong>Notes de l''utilisateur :</strong></p>
                <p style="background-color: #f5f5f5; padding: 10px; border-radius: 5px;">{{user_notes}}</p>
                {{/user_notes}}
            </div>

            <p><strong>Actions requises :</strong></p>
            <ol>
                <li>Vérifier la validité du paiement</li>
                <li>Consulter la preuve de paiement (si fournie)</li>
                <li>Approuver ou rejeter la demande</li>
            </ol>

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{admin_review_url}}" class="button">Gérer les Demandes</a>
            </p>
        </div>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement aux administrateurs.</p>
            <p>&copy; {{year}} {{site_name}}. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>',
    'Bonjour Admin,

⚠ NOUVELLE DEMANDE EN ATTENTE

Une nouvelle demande de rechargement wallet nécessite votre validation.

Informations utilisateur :
- Nom : {{user_name}}
- Email : {{user_email}}
- ID : {{user_id}}

Détails de la demande :
- N° de demande : #{{request_id}}
- Montant : {{amount}} XOF
- Méthode de paiement : {{payment_method}}
- Date de soumission : {{created_at}}
- IP utilisateur : {{ip_address}}

{{#user_notes}}
Notes de l''utilisateur :
{{user_notes}}
{{/user_notes}}

Actions requises :
1. Vérifier la validité du paiement
2. Consulter la preuve de paiement (si fournie)
3. Approuver ou rejeter la demande

Gérer les Demandes : {{admin_review_url}}

Cet email a été envoyé automatiquement aux administrateurs.
© {{year}} {{site_name}}. Tous droits réservés.',
    '["user_name", "user_email", "user_id", "request_id", "amount", "payment_method", "created_at", "ip_address", "user_notes", "admin_review_url", "site_name", "year"]',
    1
);
