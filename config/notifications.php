<?php
/**
 * YK Digital Hub — Notification credentials.
 */

// =====================================================
// EMAIL (SMTP) — Gmail
// =====================================================
define('NOTIFY_EMAIL_ENABLED', true);

define('SMTP_HOST',       'smtp.gmail.com');
define('SMTP_PORT',       587);
define('SMTP_USERNAME',   'younas447123@gmail.com');
define('SMTP_PASSWORD',   'kskiejfvmuphuyzm');
define('SMTP_ENCRYPTION', 'tls');

define('NOTIFY_FROM_EMAIL', 'younas447123@gmail.com');
define('NOTIFY_FROM_NAME',  'YK Digital Hub Website');
define('NOTIFY_TO_EMAIL',   'ykdigitalhub@outlook.com');
define('NOTIFY_TO_NAME',    'YK Digital Hub');


// =====================================================
// WHATSAPP (CallMeBot) — disabled for now
// =====================================================
define('NOTIFY_WHATSAPP_ENABLED', false);
define('CALLMEBOT_PHONE',  '923069776937');
define('CALLMEBOT_APIKEY', 'REPLACE_WITH_YOUR_APIKEY');