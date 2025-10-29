<?php
/**
 * =====================================================
 * MyWebsite Configuration File
 * =====================================================
 * Centralized configuration for database, email, and site settings.
 * Include this file at the top of any script using:
 *   require_once __DIR__ . '/config.php';
 */

// ========================
// ⚙️ SITE CONFIG
// ========================
define('SITE_NAME', 'MyWebsite');
define('BASE_URL', 'http://localhost/mywebsite/'); // Change when live
define('ADMIN_EMAIL', 'hvotranminh2003@gmail.com');

// Set UTF-8 charset
$conn->set_charset("utf8mb4");

// ========================
// 📧 EMAIL CONFIG (PHPMailer)
// ========================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'hvotranminh2003@gmail.com');
define('SMTP_PASS', 'YOUR GMAIL APP PASSWORD'); // ⚠️ Gmail App Password
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// ========================
// 🕒 OTHER SETTINGS
// ========================
// Verification link expiry (in hours)
define('VERIFICATION_EXPIRY_HOURS', 24);

// Password reset token expiry (in hours)
define('RESET_EXPIRY_HOURS', 1);
