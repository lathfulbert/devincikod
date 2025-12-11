-- Script de vérification de la base de données
-- À exécuter pour vérifier que tous les champs nécessaires existent

-- Vérifier la table sms_messages
SELECT
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'sms_messages'
  AND TABLE_SCHEMA = DATABASE()
  AND COLUMN_NAME IN ('cost', 'user_id', 'to', 'from', 'message', 'status', 'gateway_message_id', 'sent_at');

-- Si le champ 'cost' n'existe pas, le créer :
-- ALTER TABLE sms_messages ADD COLUMN cost DECIMAL(10,4) NOT NULL DEFAULT 0.0000 AFTER gateway;

-- Vérifier quelques enregistrements récents
SELECT
    id,
    user_id,
    `to`,
    `from`,
    LEFT(message, 30) as message_preview,
    status,
    cost,
    sent_at
FROM sms_messages
ORDER BY id DESC
LIMIT 10;
