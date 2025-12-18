<?php
/**
 * SMTP Email Configuration
 * Values are loaded from environment variables for security.
 */

// Load host - default to smtp.gmail.com if not set
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');
define('SMTP_AUTH', true);

// Credentials
define('SMTP_USERNAME', getenv('SMTP_USER') ?: '');
define('SMTP_PASSWORD', getenv('SMTP_PASS') ?: '');

// Sender Info
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: getenv('SMTP_USER'));
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'Sistem Kas Akademik');

// Other settings
define('EMAIL_CHARSET', 'UTF-8');
define('EMAIL_DEBUG', (int) (getenv('EMAIL_DEBUG') ?: 0));
