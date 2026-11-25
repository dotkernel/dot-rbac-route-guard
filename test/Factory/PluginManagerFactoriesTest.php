<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Factory;

use Dot\Rbac\Route\Guard\Factory\GuardPluginManagerFactory;
use Dot\Rbac\Route\Guard\Factory\GuardsProviderPluginManagerFactory;
use Dot\Rbac\Route\Guard\Factory\RbacGuardOptionsFactory;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use DotTest\Rbac\Route\Guard\Fixture\Guard;
use DotTest\Rbac\Route\Guard\Fixture\GuardsProvider;
use DotTest\Rbac\Route\Guard\Fixture\InMemoryContainer;
use PHPUnit\Framework\TestCase;

class PluginManagerFactoriesTest extends TestCase
{
    public function testGuardPluginManagerReadsConfig(): void
    {
        $container = new InMemoryContainer([
            'config' => [
                'dot_authorization' => [
                    'guard_manager' => [
                        'factories' => ['custom' => static fn (): Guard => new Guard(), 5 => 'ignored', 'bad' => 5],
                        'aliases'   => ['Custom' => 'custom', 'bad' => []],
                    ],
                ],
            ],
        ]);

        $subject = (new GuardPluginManagerFactory())($container);

        $this->assertInstanceOf(Guard::class, $subject->build('Custom'));
        $this->assertFalse($subject->has('bad'));
        $this->assertTrue($subject->has('Route'));
    }

    public function testGuardPluginManagerWorksWithoutConfig(): void
    {
        $this->assertTrue((new GuardPluginManagerFactory())(new InMemoryContainer())->has('Route'));
        $this->assertTrue(
            (new GuardPluginManagerFactory())(new InMemoryContainer([
                'config' => [
                    'dot_authorization' => ['guard_manager' => 'invalid'],
                ],
            ]))->has('Route')
        );
    }

    public function testGuardsProviderPluginManagerReadsConfig(): void
    {
        $container = new InMemoryContainer([
            'config' => [
                'dot_authorization' => [
                    'guards_provider_manager' => [
                        'factories' => ['custom' => static fn (): GuardsProvider => new GuardsProvider()],
                    ],
                ],
            ],
        ]);

        $subject = (new GuardsProviderPluginManagerFactory())($container);

        $this->assertInstanceOf(GuardsProvider::class, $subject->build('custom'));
        $this->assertTrue($subject->has('ArrayGuards'));
    }

    public function testRbacGuardOptionsFactory(): void
    {
        $options = (new RbacGuardOptionsFactory())(new InMemoryContainer([
            'config' => [
                'dot_authorization' => ['protection_policy' => GuardInterface::POLICY_DENY],
            ],
        ]));
        $this->assertSame(GuardInterface::POLICY_DENY, $options->getProtectionPolicy());

        $options = (new RbacGuardOptionsFactory())(new InMemoryContainer(['config' => []]));
        $this->assertSame(GuardInterface::POLICY_ALLOW, $options->getProtectionPolicy());

        $options = (new RbacGuardOptionsFactory())(new InMemoryContainer());
        $this->assertSame(GuardInterface::POLICY_ALLOW, $options->getProtectionPolicy());
    }
}
