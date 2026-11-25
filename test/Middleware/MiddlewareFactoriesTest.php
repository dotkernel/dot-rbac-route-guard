<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Middleware;

use Dot\Authentication\AuthenticationInterface;
use Dot\Authentication\Exception\UnauthorizedException;
use Dot\Authorization\AuthorizationInterface;
use Dot\Authorization\Exception\ForbiddenException;
use Dot\Rbac\Route\Guard\Event\AuthorizationEvent;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Factory\ForbiddenHandlerFactory;
use Dot\Rbac\Route\Guard\Factory\RbacGuardMiddlewareFactory;
use Dot\Rbac\Route\Guard\Middleware\ForbiddenHandler;
use Dot\Rbac\Route\Guard\Middleware\RbacGuardMiddleware;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use Dot\Rbac\Route\Guard\Provider\GuardsProviderPluginManager;
use DotTest\Rbac\Route\Guard\Fixture\Authentication;
use DotTest\Rbac\Route\Guard\Fixture\AuthorizationService;
use DotTest\Rbac\Route\Guard\Fixture\Guard;
use DotTest\Rbac\Route\Guard\Fixture\GuardsProvider;
use DotTest\Rbac\Route\Guard\Fixture\InMemoryContainer;
use DotTest\Rbac\Route\Guard\Fixture\Listener;
use DotTest\Rbac\Route\Guard\Fixture\Request;
use DotTest\Rbac\Route\Guard\Fixture\RequestHandler;
use DotTest\Rbac\Route\Guard\Fixture\TemplateRenderer;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

class MiddlewareFactoriesTest extends TestCase
{
    private Listener $listener;

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $services
     */
    private function container(array $config = [], array $services = []): InMemoryContainer
    {
        $this->listener = new Listener();
        $container      = new InMemoryContainer($services + [
            'config'                      => $config,
            AuthorizationInterface::class => new AuthorizationService(),
            RbacGuardOptions::class       => new RbacGuardOptions([
                'event_listeners' => ['my.listener'],
                'guards_provider' => ['type' => 'fixture'],
            ]),
            'my.listener'                 => $this->listener,
        ]);
        $container->set(GuardsProviderPluginManager::class, new GuardsProviderPluginManager($container, [
            'factories' => ['fixture' => static fn (): GuardsProvider => new GuardsProvider([new Guard(false)])],
        ]));

        return $container;
    }

    public function testForbiddenHandlerFactoryWithoutDebugKey(): void
    {
        $handler = (new ForbiddenHandlerFactory())($this->container(), ForbiddenHandler::class);

        $this->assertFalse($handler->isDebug());
    }

    public function testForbiddenHandlerFactoryReadsDebugAndTemplate(): void
    {
        $renderer  = new TemplateRenderer();
        $container = $this->container(
            ['debug' => true, 'mezzio' => ['error_handler' => ['template_403' => 'custom::403']]],
            [TemplateRendererInterface::class => $renderer]
        );

        $handler  = (new ForbiddenHandlerFactory())($container);
        $response = $handler->process(
            Request::withoutRoute(),
            new RequestHandler(static fn () => throw new ForbiddenException('no'))
        );

        $this->assertTrue($handler->isDebug());
        $this->assertSame('rendered:custom::403', (string) $response->getBody());
        $this->assertSame([AuthorizationEvent::EVENT_FORBIDDEN], $this->listener->events);
    }

    public function testForbiddenHandlerFactoryTreatsNonBooleanDebugAsFalse(): void
    {
        $handler = (new ForbiddenHandlerFactory())($this->container(['debug' => 'yes']));

        $this->assertFalse($handler->isDebug());
    }

    public function testForbiddenHandlerFactoryRejectsOtherClasses(): void
    {
        $this->expectException(RuntimeException::class);
        (new ForbiddenHandlerFactory())($this->container(), stdClass::class);
    }

    public function testForbiddenHandlerFactoryRequiresAuthorizationService(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service ' . AuthorizationInterface::class . ' is not configured');
        (new ForbiddenHandlerFactory())($this->container([], [AuthorizationInterface::class => null]));
    }

    public function testRbacGuardMiddlewareFactoryBuildsWorkingMiddleware(): void
    {
        $middleware = (new RbacGuardMiddlewareFactory())($this->container(), RbacGuardMiddleware::class);

        try {
            $middleware->process(Request::forRoute('page'), new RequestHandler());
            $this->fail('A ForbiddenException was expected');
        } catch (ForbiddenException) {
            $this->assertSame(
                [AuthorizationEvent::EVENT_BEFORE_AUTHORIZATION, AuthorizationEvent::EVENT_AFTER_AUTHORIZATION],
                $this->listener->events
            );
        }
    }

    public function testRbacGuardMiddlewareFactoryUsesAuthentication(): void
    {
        $container = $this->container([], [AuthenticationInterface::class => new Authentication()]);

        $this->expectException(UnauthorizedException::class);
        (new RbacGuardMiddlewareFactory())($container)->process(Request::forRoute('page'), new RequestHandler());
    }

    public function testRbacGuardMiddlewareFactoryRejectsOtherClasses(): void
    {
        $this->expectException(RuntimeException::class);
        (new RbacGuardMiddlewareFactory())($this->container(), stdClass::class);
    }

    public function testRbacGuardMiddlewareFactoryRequiresAuthorizationService(): void
    {
        $this->expectException(RuntimeException::class);
        (new RbacGuardMiddlewareFactory())($this->container([], [AuthorizationInterface::class => null]));
    }
}
