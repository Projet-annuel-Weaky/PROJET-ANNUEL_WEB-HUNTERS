<?php
require_once __DIR__ . '/../../configs/config.php';

if (!function_exists('logAction')) {
    function logAction($message) {
        error_log('[captcha] ' . $message);
    }
}