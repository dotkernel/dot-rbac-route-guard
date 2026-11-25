<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Middleware;

use Dot\Authorization\Exception\ForbiddenException;
use Dot\Rbac\Route\Guard\Event\AuthorizationEvent;
use Dot\Rbac\Route\Guard\Middleware\ForbiddenHandler;
use Dot\Rbac\Route\Guard\Options\MessagesOptions;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use DotTest\Rbac\Route\Guard\Fixture\AuthorizationService;
use DotTest\Rbac\Route\Guard\Fixture\Request;
use DotTest\Rbac\Route\Guard\Fixture\RequestHandler;
use DotTest\Rbac\Route\Guard\Fixture\TemplateRenderer;
use Laminas\Diactoros\Response\TextResponse;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ForbiddenHandlerTest extends TestCase
{
    private function handler(?TemplateRenderer $renderer = null): ForbiddenHandler
    {
        return new ForbiddenHandler(new AuthorizationService(), new RbacGuardOptions(), $renderer, 'app::403');
    }

    private function throwing(string $message = 'Not allowed'): RequestHandler
    {
        return new RequestHandler(static fn () => throw new ForbiddenException($message));
    }

    public function testPassesResponseThrough(): void
    {
        $response = $this->handler()->process(Request::withoutRoute(), new RequestHandler());
        $this->assertSame('handled', (string) $response->getBody());
    }

    public function testOtherExceptionsAreNotHandled(): void
    {
        $handler = new RequestHandler(static fn () => throw new RuntimeException('upstream said 403', 403));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('upstream said 403');
        $this->handler(new TemplateRenderer())->process(Request::withoutRoute(), $handler);
    }

    public function testWithoutRendererRethrowsWithPreviousException(): void
    {
        try {
            $this->handler()->process(Request::withoutRoute(), $this->throwing());
            $this->fail('A ForbiddenException was expected');
        } catch (ForbiddenException $e) {
            $this->assertSame('Not allowed', $e->getMessage());
            $this->assertSame(403, $e->getCode());
            $this->assertInstanceOf(ForbiddenException::class, $e->getPrevious());
        }
    }

    public function testRendersTemplateWith403(): void
    {
        $renderer = new TemplateRenderer();

        $response = $this->handler($renderer)->process(Request::withoutRoute(), $this->throwing());

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('rendered:app::403', (string) $response->getBody());
        $this->assertIsArray($renderer->lastParams);
        $this->assertSame('Not allowed', $renderer->lastParams['message']);
        $this->assertSame(403, $renderer->lastParams['status']);
        $this->assertArrayNotHasKey('error', $renderer->lastParams);
    }

    public function testEmptyMessageUsesConfiguredMessage(): void
    {
        $renderer = new TemplateRenderer();

        $this->handler($renderer)->process(Request::withoutRoute(), $this->throwing(''));

        $this->assertIsArray($renderer->lastParams);
        $this->assertSame(
            (new MessagesOptions())->getMessage(MessagesOptions::FORBIDDEN),
            $renderer->lastParams['message']
        );
    }

    public function testDebugAddsTheError(): void
    {
        $renderer = new TemplateRenderer();
        $subject  = $this->handler($renderer);
        $subject->setDebug(true);

        $subject->process(Request::withoutRoute(), $this->throwing());

        $this->assertTrue($subject->isDebug());
        $this->assertIsArray($renderer->lastParams);
        $this->assertInstanceOf(ForbiddenException::class, $renderer->lastParams['error']);
    }

    public function testListenerResponseIsReturned(): void
    {
        $subject = $this->handler();
        $subject->getEventManager()->attach(
            AuthorizationEvent::EVENT_FORBIDDEN,
            static fn (): TextResponse => new TextResponse('redirect', 302)
        );

        $response = $subject->process(Request::withoutRoute(), $this->throwing());

        $this->assertSame(302, $response->getStatusCode());
    }
}
