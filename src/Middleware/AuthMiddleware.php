<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class AuthMiddleware
{
    public function handle(Request $request, Response $response, callable $next): Response
    {
        if ($request->getSession("Authorization") == '') {
            $response->setContent('Unauthorized');
            $response->setStatusCode(401);
            return $response;
        }

        return $next($request, $response);
    }
}
