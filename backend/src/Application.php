<?php
declare(strict_types=1);

namespace App;

use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\Middleware\SessionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;
use App\Middleware\SimpleSessionMiddleware;

class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    public function bootstrap(): void
    {
        parent::bootstrap();

        if (PHP_SAPI !== 'cli') {
            FactoryLocator::add(
                'Table',
                (new TableLocator())->allowFallbackClass(false)
            );
        }

        $this->addPlugin('Authentication');

        if (Configure::read('debug') && class_exists('DebugKit\\Plugin')) {
            $this->addPlugin('DebugKit');
        }
    }

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        // Ensure Router's route collection is initialized before any
        // middleware attempts to access it.
        Router::reload();

        $middlewareQueue
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))
            // Short-circuit special paths before routing
            ->add(new \App\Middleware\WellKnownMiddleware())
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))
            ->add(new RoutingMiddleware($this))
            ->add(new BodyParserMiddleware())
            ->add(new SimpleSessionMiddleware())
            ->add(new AuthenticationMiddleware($this));

        return $middlewareQueue;
    }

    public function services(ContainerInterface $container): void
    {
    }

    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        // Avoid calling Router::url() here because Router may not be
        // initialized when the authentication middleware asks for the
        // service. Use a literal path instead.
        $loginUrl = '/login';

        $service = new AuthenticationService([
            'unauthenticatedRedirect' => $loginUrl,
            'queryParam' => 'redirect',
            'authenticators' => [
                'Authentication.Session',
                'Authentication.Form' => [
                    'fields' => [
                        'username' => 'username',
                        'password' => 'password',
                    ],
                    'loginUrl' => null, // Allow login from any URL
                ],
            ],
            'identifiers' => [
                'Authentication.Password' => [
                    'fields' => [
                        'username' => 'username',
                        'password' => 'password_hash',
                    ],
                    'resolver' => [
                        'className' => 'Authentication.Orm',
                        'userModel' => 'Users',
                    ],
                ],
            ],
        ]);

        // Return the configured service directly.
        return $service;
    }
}
