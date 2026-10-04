<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/notifications.php';

echo "<h1>Email Diagnostic</h1>";
echo "<p><strong>Host:</strong> " . SMTP_HOST . " <strong>Port:</strong> " . SMTP_PORT . " <strong>Encryption:</strong> " . SMTP_ENCRYPTION . "</p>";
echo "<p><strong>User:</strong> " . SMTP_USERNAME . "</p>";
echo "<p><strong>Password length:</strong> " . strlen(SMTP_PASSWORD) . " chars</p>";
echo "<hr>";

$base = __DIR__ . '/includes/PHPMailer/';
require_once $base . 'Exception.php';
require_once $base . 'PHPMailer.php';
require_once $base . 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

try {
    // Enable verbose debug output so we see the SMTP conversation
    $mail->SMTPDebug   = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo "<div style='font-family:monospace; font-size:12px; color:#555;'>" . htmlspecialchars($str) . "</div>";
    };

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

    $mail->isHTML(true);
    $mail->Subject = 'YK Digital Hub — SMTP Test';
    $mail->Body    = '<p>If you are reading this, your SMTP settings work.</p>';
    $mail->AltBody = 'If you are reading this, your SMTP settings work.';

    $mail->send();
    echo "<p style='font-size:20px; color:green;'><strong>✅ Email sent successfully!</strong></p>";
} catch (PHPMailerException $e) {
    echo "<hr><p style='font-size:18px; color:red;'><strong>❌ Error:</strong> " . htmlspecialchars($mail->ErrorInfo) . "</p>";
} catch (\Throwable $e) {
    echo "<hr><p style='font-size:18px; color:red;'><strong>❌ Fatal:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}