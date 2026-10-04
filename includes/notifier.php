<?php
/**
 * Contact form notification helpers.
 * Sends email (via PHPMailer SMTP) + WhatsApp (via CallMeBot).
 * Both are optional and fail gracefully — they never block the form submission.
 */

if (!defined('NOTIFY_EMAIL_ENABLED')) {
    $cfg = __DIR__ . '/../config/notifications.php';
    if (file_exists($cfg)) require_once $cfg;
}

/**
 * Send the contact submission by email.
 *
 * @param array $data  ['name','email','phone','company','subject','message']
 * @return bool
 */
function notifyByEmail(array $data) {
    if (!defined('NOTIFY_EMAIL_ENABLED') || !NOTIFY_EMAIL_ENABLED) return false;
    if (empty(SMTP_USERNAME) || SMTP_PASSWORD === 'REPLACE_WITH_YOUR_APP_PASSWORD') {
        error_log('[YK Notifier] Email skipped: SMTP credentials not configured.');
        return false;
    }

    $phpmailerBase = __DIR__ . '/PHPMailer/';
    if (!file_exists($phpmailerBase . 'PHPMailer.php')) {
        error_log('[YK Notifier] Email skipped: PHPMailer not found at ' . $phpmailerBase);
        return false;
    }

    require_once $phpmailerBase . 'Exception.php';
    require_once $phpmailerBase . 'PHPMailer.php';
    require_once $phpmailerBase . 'SMTP.php';

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(NOTIFY_FROM_EMAIL, NOTIFY_FROM_NAME);
        $mail->addAddress(NOTIFY_TO_EMAIL, NOTIFY_TO_NAME);
        $mail->addReplyTo($data['email'], $data['name']);

        $mail->isHTML(true);
        $mail->Subject = 'New contact form message — ' . $data['name'];
        $mail->Body    = yk_build_email_html($data);
        $mail->AltBody = yk_build_email_text($data);

        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log('[YK Notifier] Email failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send the contact submission by WhatsApp (CallMeBot).
 *
 * @param array $data
 * @return bool
 */
function notifyByWhatsApp(array $data) {
    if (!defined('NOTIFY_WHATSAPP_ENABLED') || !NOTIFY_WHATSAPP_ENABLED) return false;
    if (empty(CALLMEBOT_PHONE) || CALLMEBOT_APIKEY === 'REPLACE_WITH_YOUR_APIKEY') {
        error_log('[YK Notifier] WhatsApp skipped: CallMeBot not configured.');
        return false;
    }

    $text  = "*New Contact Form Submission*\n";
    $text .= "————————————\n";
    $text .= "👤 *Name:* "    . $data['name'] . "\n";
    $text .= "✉️ *Email:* "   . $data['email'] . "\n";
    if (!empty($data['phone']))   $text .= "📞 *Phone:* "   . $data['phone'] . "\n";
    if (!empty($data['company'])) $text .= "🏢 *Company:* " . $data['company'] . "\n";
    if (!empty($data['subject'])) $text .= "🛠 *Services:* " . $data['subject'] . "\n";
    $text .= "————————————\n";
    $text .= "💬 *Message:*\n" . $data['message'];

    $url = 'https://api.callmebot.com/whatsapp.php?' . http_build_query([
        'phone'  => CALLMEBOT_PHONE,
        'text'   => $text,
        'apikey' => CALLMEBOT_APIKEY,
    ]);

    // Use cURL if available, else file_get_contents
    try {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code >= 200 && $code < 300) return true;
            error_log('[YK Notifier] WhatsApp HTTP ' . $code . ' — ' . substr((string)$resp, 0, 200));
            return false;
        }
        $ctx = stream_context_create(['http' => ['timeout' => 10]]);
        $resp = @file_get_contents($url, false, $ctx);
        return $resp !== false;
    } catch (\Throwable $e) {
        error_log('[YK Notifier] WhatsApp failed: ' . $e->getMessage());
        return false;
    }
}

/** HTML email body */
function yk_build_email_html(array $d) {
    $e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    return '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color:#1e293b;">
      <div style="background:linear-gradient(90deg,#0084FF,#0066CC); color:#fff; padding:20px 24px; border-radius:8px 8px 0 0;">
        <h2 style="margin:0; font-size:20px;">New Contact Form Submission</h2>
        <p style="margin:4px 0 0; font-size:13px; opacity:.9;">From your YK Digital Hub website</p>
      </div>
      <div style="background:#fff; border:1px solid #e2e8f0; border-top:none; padding:24px; border-radius:0 0 8px 8px;">
        <table style="width:100%; border-collapse:collapse; font-size:14px; line-height:1.6;">
          <tr><td style="padding:6px 0; width:120px; color:#64748b;">Name</td><td style="padding:6px 0; font-weight:600;">' . $e($d['name']) . '</td></tr>
          <tr><td style="padding:6px 0; color:#64748b;">Email</td><td style="padding:6px 0;"><a href="mailto:' . $e($d['email']) . '" style="color:#0084FF;">' . $e($d['email']) . '</a></td></tr>
          ' . (!empty($d['phone'])   ? '<tr><td style="padding:6px 0; color:#64748b;">Phone</td><td style="padding:6px 0;">' . $e($d['phone']) . '</td></tr>' : '') . '
          ' . (!empty($d['company']) ? '<tr><td style="padding:6px 0; color:#64748b;">Company</td><td style="padding:6px 0;">' . $e($d['company']) . '</td></tr>' : '') . '
          ' . (!empty($d['subject']) ? '<tr><td style="padding:6px 0; color:#64748b;">Services</td><td style="padding:6px 0;">' . $e($d['subject']) . '</td></tr>' : '') . '
        </table>
        <hr style="border:none; border-top:1px solid #e2e8f0; margin:20px 0;">
        <h3 style="font-size:14px; margin:0 0 8px; color:#64748b; text-transform:uppercase; letter-spacing:.05em;">Message</h3>
        <div style="background:#f8fafc; border-left:3px solid #0084FF; padding:14px 16px; border-radius:4px; font-size:15px; line-height:1.7; white-space:pre-wrap;">' . $e($d['message']) . '</div>
        <p style="font-size:12px; color:#94a3b8; margin-top:24px;">Received on ' . date('M j, Y \a\t g:i a') . '</p>
      </div>
    </div>';
}

/** Plain-text fallback */
function yk_build_email_text(array $d) {
    $lines = [
        'NEW CONTACT FORM SUBMISSION',
        '----------------------------------',
        'Name:     ' . $d['name'],
        'Email:    ' . $d['email'],
    ];
    if (!empty($d['phone']))   $lines[] = 'Phone:    ' . $d['phone'];
    if (!empty($d['company'])) $lines[] = 'Company:  ' . $d['company'];
    if (!empty($d['subject'])) $lines[] = 'Services: ' . $d['subject'];
    $lines[] = '----------------------------------';
    $lines[] = 'MESSAGE:';
    $lines[] = $d['message'];
    $lines[] = '';
    $lines[] = 'Received on ' . date('M j, Y \a\t g:i a');
    return implode("\n", $lines);
}