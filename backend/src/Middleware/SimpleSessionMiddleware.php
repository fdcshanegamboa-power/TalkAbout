<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Session;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;

class SimpleSessionMiddleware implements MiddlewareInterface
{
    /**
     * Attach a CakePHP Session instance to the request as the `session` attribute.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = Session::create();
        try {
            $session->start();
        } catch (\Throwable $e) {
            // continue if session cannot be started (headers sent, etc.)
        }

        $request = $request->withAttribute('session', $session);

        return $handler->handle($request);
    }
}
