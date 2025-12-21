<?php
/**
 * SMTP Email Configuration
 * Values are loaded from environment variables for security.
 */


function getEmailConfigValue($key, $default = '')
{
    if (isset($_ENV[$key]))
        return $_ENV[$key];
    if (isset($_SERVER[$key]))
        return $_SERVER[$key];
    $val = getenv($key);
    return ($val !== false) ? $val : $default;
}


define('SMTP_HOST', getEmailConfigValue('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', getEmailConfigValue('SMTP_PORT', 587));
define('SMTP_SECURE', getEmailConfigValue('SMTP_SECURE', 'tls'));
define('SMTP_AUTH', true);


define('SMTP_USERNAME', getEmailConfigValue('SMTP_USER', ''));
define('SMTP_PASSWORD', getEmailConfigValue('SMTP_PASS', ''));


define('SMTP_FROM_EMAIL', getEmailConfigValue('SMTP_FROM_EMAIL', getEmailConfigValue('SMTP_USER')));
define('SMTP_FROM_NAME', getEmailConfigValue('SMTP_FROM_NAME', 'Sistem Kas Akademik'));


define('EMAIL_CHARSET', 'UTF-8');
define('EMAIL_DEBUG', (int) getEmailConfigValue('EMAIL_DEBUG', 0));
