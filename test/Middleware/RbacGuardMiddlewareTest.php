<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Middleware;

use Dot\Authentication\Exception\UnauthorizedException;
use Dot\Authorization\Exception\ForbiddenException;
use Dot\Rbac\Route\Guard\Event\AuthorizationEvent;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Dot\Rbac\Route\Guard\Middleware\RbacGuardMiddleware;
use Dot\Rbac\Route\Guard\Options\MessagesOptions;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use DotTest\Rbac\Route\Guard\Fixture\Authentication;
use DotTest\Rbac\Route\Guard\Fixture\AuthorizationService;
use DotTest\Rbac\Route\Guard\Fixture\Guard;
use DotTest\Rbac\Route\Guard\Fixture\GuardsProvider;
use DotTest\Rbac\Route\Guard\Fixture\Identity;
use DotTest\Rbac\Route\Guard\Fixture\Request;
use DotTest\Rbac\Route\Guard\Fixture\RequestHandler;
use Laminas\Diactoros\Response\TextResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class RbacGuardMiddlewareTest extends TestCase
{
    /**
     * @param array<mixed> $guards
     */
    private function middleware(
        array $guards,
        string $policy = GuardInterface::POLICY_ALLOW,
        ?Authentication $authentication = null
    ): RbacGuardMiddleware {
        return new RbacGuardMiddleware(
            new AuthorizationService(),
            new GuardsProvider($guards),
            new RbacGuardOptions(['protection_policy' => $policy]),
            $authentication
        );
    }

    /**
     * @return iterable<string, array{string, bool, bool, bool}>
     */
    public static function guardResultProvider(): iterable
    {
        foreach ([GuardInterface::POLICY_ALLOW, GuardInterface::POLICY_DENY] as $policy) {
            yield "$policy, grant + grant" => [$policy, true, true, true];
            yield "$policy, grant + deny"  => [$policy, true, false, false];
            yield "$policy, deny + grant"  => [$policy, false, true, false];
            yield "$policy, deny + deny"   => [$policy, false, false, false];
        }
    }

    #[DataProvider('guardResultProvider')]
    public function testEveryGuardMustGrantAccess(string $policy, bool $first, bool $second, bool $expected): void
    {
        $subject = $this->middleware([new Guard($first), new Guard($second)], $policy);
        $handler = new RequestHandler();

        if (! $expected) {
            $this->expectException(ForbiddenException::class);
            $this->expectExceptionCode(403);
        }

        $response = $subject->process(Request::forRoute('page'), $handler);
        $this->assertSame('handled', (string) $response->getBody());
    }

    public function testStopsAtFirstDenyingGuard(): void
    {
        $last    = new Guard(true);
        $subject = $this->middleware([new Guard(false), $last]);

        try {
            $subject->process(Request::forRoute('page'), new RequestHandler());
            $this->fail('A ForbiddenException was expected');
        } catch (ForbiddenException) {
            $this->assertSame(0, $last->calls);
        }
    }

    public function testWithoutGuardsTheGlobalPolicyDecides(): void
    {
        $response = $this->middleware([])->process(Request::forRoute('page'), new RequestHandler());
        $this->assertSame('handled', (string) $response->getBody());

        $this->expectException(ForbiddenException::class);
        $this->middleware([], GuardInterface::POLICY_DENY)->process(Request::forRoute('page'), new RequestHandler());
    }

    public function testRequestWithoutRouteResultIsAuthorized(): void
    {
        $guard    = new Guard(false);
        $response = $this->middleware([$guard], GuardInterface::POLICY_DENY)
            ->process(Request::withoutRoute(), new RequestHandler());

        $this->assertSame('handled', (string) $response->getBody());
        $this->assertSame(0, $guard->calls);
    }

    public function testGuestGetsUnauthorizedException(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage((new MessagesOptions())->getMessage(MessagesOptions::UNAUTHORIZED));

        $this->middleware([new Guard(false)], GuardInterface::POLICY_ALLOW, new Authentication())
            ->process(Request::forRoute('page'), new RequestHandler());
    }

    public function testIdentityGetsForbiddenException(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage((new MessagesOptions())->getMessage(MessagesOptions::FORBIDDEN));

        $this->middleware([new Guard(false)], GuardInterface::POLICY_ALLOW, new Authentication(new Identity()))
            ->process(Request::forRoute('page'), new RequestHandler());
    }

    public function testObjectThatIsNotAGuardThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Guard is not an instance of ' . GuardInterface::class);
        $this->middleware(['not a guard'])->process(Request::forRoute('page'), new RequestHandler());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function eventProvider(): iterable
    {
        yield 'before' => [AuthorizationEvent::EVENT_BEFORE_AUTHORIZATION];
        yield 'after'  => [AuthorizationEvent::EVENT_AFTER_AUTHORIZATION];
    }

    #[DataProvider('eventProvider')]
    public function testListenerResponseShortCircuits(string $eventName): void
    {
        $subject = $this->middleware([new Guard(false)]);
        $subject->getEventManager()->attach($eventName, static fn (): TextResponse => new TextResponse('stop', 418));
        $handler = new RequestHandler();

        $response = $subject->process(Request::forRoute('page'), $handler);

        $this->assertSame(418, $response->getStatusCode());
        $this->assertNull($handler->request);
    }

    public function testAfterListenerCanChangeTheDecisionAndTheRequest(): void
    {
        $subject = $this->middleware([new Guard(false)]);
        $subject->getEventManager()->attach(
            AuthorizationEvent::EVENT_AFTER_AUTHORIZATION,
            static function (AuthorizationEvent $event): void {
                $request = $event->getParam('request');
                $event->setParam('authorized', true);
                if ($request instanceof ServerRequestInterface) {
                    $event->setParam('request', $request->withAttribute('changed', true));
                }
            }
        );
        $handler = new RequestHandler();

        $subject->process(Request::forRoute('page'), $handler);

        $this->assertTrue($handler->request?->getAttribute('changed'));
    }
}
