<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Guard;

use Dot\Rbac\Role\RoleServiceInterface;
use Dot\Rbac\Route\Guard\Exception\InvalidArgumentException;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Guard\Factory;
use Dot\Rbac\Route\Guard\Guard\GuardPluginManager;
use Dot\Rbac\Route\Guard\Guard\RouteGuard;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use DotTest\Rbac\Route\Guard\Fixture\InMemoryContainer;
use DotTest\Rbac\Route\Guard\Fixture\RoleService;
use PHPUnit\Framework\TestCase;

class GuardFactoryTest extends TestCase
{
    private InMemoryContainer $container;

    protected function setUp(): void
    {
        $this->container = new InMemoryContainer([
            RoleServiceInterface::class => new RoleService(),
            RbacGuardOptions::class     => new RbacGuardOptions(),
        ]);
    }

    public function testCreateRequiresType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Guard type was not provided');
        (new Factory($this->container))->create([]);
    }

    public function testCreateRejectsNonStringType(): void
    {
        $this->expectException(RuntimeException::class);
        (new Factory($this->container))->create(['type' => ['Route']]);
    }

    public function testCreateBuildsGuardFromAlias(): void
    {
        $guard = (new Factory($this->container))->create([
            'type'    => 'Route',
            'options' => ['rules' => ['home' => ['*']]],
        ]);

        $this->assertInstanceOf(RouteGuard::class, $guard);
        $this->assertSame(['home' => ['*']], $guard->getRules());
    }

    public function testCreateWithUnknownTypeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new Factory($this->container))->create(['type' => 'Unknown']);
    }

    public function testUsesGivenPluginManager(): void
    {
        $pluginManager = new GuardPluginManager($this->container);
        $subject       = new Factory($this->container, $pluginManager);

        $this->assertSame($pluginManager, $subject->getGuardPluginManager());
    }

    public function testCreatesPluginManagerWhenNotGiven(): void
    {
        $subject = new Factory($this->container);

        $this->assertSame($subject->getGuardPluginManager(), $subject->getGuardPluginManager());
    }
}
