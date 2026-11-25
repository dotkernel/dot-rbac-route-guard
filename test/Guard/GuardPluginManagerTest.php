<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Guard;

use Dot\Authorization\AuthorizationInterface;
use Dot\Rbac\Role\RoleServiceInterface;
use Dot\Rbac\Route\Guard\Exception\InvalidArgumentException;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Dot\Rbac\Route\Guard\Guard\GuardPluginManager;
use Dot\Rbac\Route\Guard\Guard\RouteGuard;
use Dot\Rbac\Route\Guard\Guard\RoutePermissionGuard;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use DotTest\Rbac\Route\Guard\Fixture\AuthorizationService;
use DotTest\Rbac\Route\Guard\Fixture\Guard;
use DotTest\Rbac\Route\Guard\Fixture\InMemoryContainer;
use DotTest\Rbac\Route\Guard\Fixture\RoleService;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

use function sprintf;

class GuardPluginManagerTest extends TestCase
{
    private InMemoryContainer $container;

    protected function setUp(): void
    {
        $this->container = new InMemoryContainer([
            RoleServiceInterface::class   => new RoleService(),
            AuthorizationInterface::class => new AuthorizationService(),
            RbacGuardOptions::class       => new RbacGuardOptions(),
        ]);
    }

    public function testBuildsDefaultGuardsByAliasAndClassName(): void
    {
        $subject = new GuardPluginManager($this->container);

        $this->assertInstanceOf(RouteGuard::class, $subject->build('route'));
        $this->assertInstanceOf(RouteGuard::class, $subject->build(RouteGuard::class));
        $this->assertInstanceOf(RoutePermissionGuard::class, $subject->build('RoutePermission'));
        $this->assertTrue($subject->has('routeGuard'));
        $this->assertFalse($subject->has('unknown'));
    }

    public function testConfigAddsFactoriesAndAliases(): void
    {
        $subject = new GuardPluginManager($this->container, [
            'factories' => [
                Guard::class => static fn (ContainerInterface $container, string $name, ?array $options): Guard
                    => new Guard((bool) ($options['granted'] ?? true)),
            ],
            'aliases'   => ['custom' => Guard::class],
        ]);

        $guard = $subject->build('custom', ['granted' => false]);
        $this->assertInstanceOf(Guard::class, $guard);
    }

    public function testUnknownPluginThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('No factory is registered for plugin "unknown" in %s', GuardPluginManager::class)
        );
        (new GuardPluginManager($this->container))->build('unknown');
    }

    public function testFactoryThatIsNotCallableThrows(): void
    {
        $subject = new GuardPluginManager($this->container, ['factories' => ['bad' => 'Not\\A\\Factory']]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The factory for plugin "bad" is not callable');
        $subject->build('bad');
    }

    public function testFactoryThatReturnsWrongTypeThrows(): void
    {
        $subject = new GuardPluginManager($this->container, [
            'factories' => ['bad' => static fn (): stdClass => new stdClass()],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            '%s can only create instances of %s; stdClass is invalid',
            GuardPluginManager::class,
            GuardInterface::class
        ));
        $subject->build('bad');
    }
}
