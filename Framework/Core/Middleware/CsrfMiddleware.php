<?php

namespace Framework\Core\Middleware;

use Framework\Core\App;
use Framework\Http\HttpException;
use Framework\Http\Request;

class CsrfMiddleware
{
    /**
     * Validate CSRF token for mutating requests (POST, PUT, PATCH, DELETE)
     * Throws HttpException(403) if invalid.
     *
     * @param App $app
     * @param Request $request
     * @return void
     * @throws HttpException
     */
    public static function handle(App $app, Request $request): void
    {
        $method = strtoupper((string)$request->server('REQUEST_METHOD'));
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return; // only validate mutating methods
        }

        // Accept token from POST body or X-CSRF-TOKEN header
        $csrf = $request->post('csrf_token') ?? $request->server('HTTP_X_CSRF_TOKEN') ?? null;

        // try alternative header name (some clients send lowercase)
        if ($csrf === null) {
            $csrf = $request->server('HTTP_X_CSRF_TOKEN') ?? $request->server('X-CSRF-TOKEN') ?? null;
        }

        $session = $app->getSession();
        $sessionCsrf = $session->get('csrf_token') ?? null;

        if (!$csrf || !$sessionCsrf || !is_string($csrf) || !is_string($sessionCsrf) || !hash_equals((string)$sessionCsrf, (string)$csrf)) {
            throw new HttpException(403, 'Invalid CSRF token');
        }
    }
}

