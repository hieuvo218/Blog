<?php
/**
 * =====================================================
 * MyWebsite Configuration File
 * =====================================================
 * Centralized configuration for database, email, and site settings.
 * Include this file at the top of any script using:
 *   require_once __DIR__ . '/config.php';
 */

// Load key-value pairs from ../.env if present.
$envPath = __DIR__ . '/../.env';
if (is_file($envPath) && is_readable($envPath)) {
	$envValues = parse_ini_file($envPath, false, INI_SCANNER_RAW);
	if (is_array($envValues)) {
		foreach ($envValues as $key => $value) {
			if (getenv($key) === false) {
				putenv($key . '=' . $value);
				$_ENV[$key] = $value;
			}
		}
	}
}

function env_value($key, $default = '')
{
	$value = getenv($key);
	if ($value === false || $value === '') {
		return $default;
	}
	return $value;
}

// ========================
// ⚙️ SITE CONFIG
// ========================
define('SITE_NAME', 'MyWebsite');
define('BASE_URL', 'http://localhost/mywebsite/'); // Change when live
define('ADMIN_EMAIL', 'hvotranminh2003@gmail.com');

// Set UTF-8 charset

// ========================
// 📧 EMAIL CONFIG (PHPMailer)
// ========================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'hvotranminh2003@gmail.com');
define('SMTP_PASS', env_value('SMTP_PASS')); // Store in .env
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// ========================
// 🔐 OAUTH CONFIG
// ========================
define('GOOGLE_CLIENT_ID', env_value('GOOGLE_CLIENT_ID'));
define('GOOGLE_CLIENT_SECRET', env_value('GOOGLE_CLIENT_SECRET'));

// ========================
// 🕒 OTHER SETTINGS
// ========================
// Verification link expiry (in hours)
define('VERIFICATION_EXPIRY_HOURS', 24);

// Password reset token expiry (in hours)
define('RESET_EXPIRY_HOURS', 1);
