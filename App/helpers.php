<?php

use Framework\Http\Session;

if (!function_exists('csrf_token')) {
    /**
     * Return current session CSRF token (generate if missing)
     * @return string
     */
    function csrf_token(): string
    {
        // Session MUST be started by front controller / app bootstrap
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('Session not started: start session in bootstrap before using csrf_token()');
        }
        if (empty($_SESSION['csrf_token'])) {
            try {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } catch (\Throwable $e) {
                $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
            }
        }
        return (string)($_SESSION['csrf_token'] ?? '');
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Return hidden input field for CSRF token
     * @return string
     */
    function csrf_field(): string
    {
        $token = htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('csrf_meta')) {
    function csrf_meta(): string
    {
        $token = htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return '<meta name="csrf-token" content="' . $token . '">';
    }
}
