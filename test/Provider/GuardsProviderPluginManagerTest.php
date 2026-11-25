<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Provider;

use Dot\Rbac\Route\Guard\Exception\InvalidArgumentException;
use Dot\Rbac\Route\Guard\Provider\ArrayGuardsProvider;
use Dot\Rbac\Route\Guard\Provider\GuardsProviderInterface;
use Dot\Rbac\Route\Guard\Provider\GuardsProviderPluginManager;
use DotTest\Rbac\Route\Guard\Fixture\GuardsProvider;
use DotTest\Rbac\Route\Guard\Fixture\InMemoryContainer;
use PHPUnit\Framework\TestCase;

use function sprintf;

class GuardsProviderPluginManagerTest extends TestCase
{
    public function testBuildsArrayGuardsProvider(): void
    {
        $subject = new GuardsProviderPluginManager(new InMemoryContainer());

        $this->assertInstanceOf(ArrayGuardsProvider::class, $subject->build('ArrayGuards'));
        $this->assertInstanceOf(ArrayGuardsProvider::class, $subject->build('arrayGuardsProvider', ['guards' => []]));
    }

    public function testConfigAddsFactories(): void
    {
        $subject = new GuardsProviderPluginManager(new InMemoryContainer(), [
            'factories' => ['custom' => static fn (): GuardsProvider => new GuardsProvider()],
        ]);

        $this->assertInstanceOf(GuardsProvider::class, $subject->build('custom'));
    }

    public function testValidateRejectsInvalidPlugin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            '%s can only create instances of %s; string is invalid',
            GuardsProviderPluginManager::class,
            GuardsProviderInterface::class
        ));
        (new GuardsProviderPluginManager(new InMemoryContainer(), [
            'factories' => ['bad' => static fn (): string => 'invalid'],
        ]))->build('bad');
    }
}
