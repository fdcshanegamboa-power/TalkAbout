<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\EmptyResponse;

class WellKnownMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if (str_starts_with($path, '/.well-known')) {
            // Return 404 quickly to avoid routing into Cake controllers
            return new EmptyResponse(404);
        }

        return $handler->handle($request);
    }
}
