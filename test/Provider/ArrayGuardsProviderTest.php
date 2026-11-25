<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Provider;

use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Guard\Factory;
use Dot\Rbac\Route\Guard\Guard\GuardPluginManager;
use Dot\Rbac\Route\Guard\Provider\ArrayGuardsProvider;
use DotTest\Rbac\Route\Guard\Fixture\Guard;
use DotTest\Rbac\Route\Guard\Fixture\InMemoryContainer;
use PHPUnit\Framework\TestCase;

class ArrayGuardsProviderTest extends TestCase
{
    private Factory $factory;

    protected function setUp(): void
    {
        $container     = new InMemoryContainer();
        $this->factory = new Factory($container, new GuardPluginManager($container, [
            'factories' => [
                'low'  => static fn (): Guard => new Guard(true, 1),
                'high' => static fn (): Guard => new Guard(true, 100),
            ],
        ]));
    }

    public function testConstructorRequiresGuardFactory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Guard factory is required and was not set');
        new ArrayGuardsProvider(['guard_factory' => 'not a factory']);
    }

    public function testNoGuardsConfigReturnsNoGuards(): void
    {
        $subject = new ArrayGuardsProvider(['guard_factory' => $this->factory]);

        $this->assertSame([], $subject->getGuards());
        $this->assertSame($this->factory, $subject->getGuardFactory());
    }

    public function testCreatesGuardsSortedByPriorityAndCachesThem(): void
    {
        $subject = new ArrayGuardsProvider([
            'guard_factory' => $this->factory,
            'guards'        => [['type' => 'low'], ['type' => 'high']],
        ]);

        $guards = $subject->getGuards();
        $this->assertCount(2, $guards);
        $this->assertSame(100, $guards[0]->getPriority());
        $this->assertSame(1, $guards[1]->getPriority());
        $this->assertSame($guards, $subject->getGuards());
    }

    public function testSetGuardsConfigClearsTheCache(): void
    {
        $subject = new ArrayGuardsProvider(['guard_factory' => $this->factory, 'guards' => [['type' => 'low']]]);
        $this->assertCount(1, $subject->getGuards());

        $subject->setGuardsConfig([['type' => 'low'], ['type' => 'high']]);
        $this->assertSame([['type' => 'low'], ['type' => 'high']], $subject->getGuardsConfig());
        $this->assertCount(2, $subject->getGuards());
    }

    public function testInvalidGuardConfigThrows(): void
    {
        $subject = new ArrayGuardsProvider(['guard_factory' => $this->factory, 'guards' => ['low']]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Each guard config must be an array with a "type" key');
        $subject->getGuards();
    }
}
