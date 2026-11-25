<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Options;

use Dot\Rbac\Route\Guard\Exception\InvalidArgumentException;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Dot\Rbac\Route\Guard\Options\MessagesOptions;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use PHPUnit\Framework\TestCase;

class RbacGuardOptionsTest extends TestCase
{
    public function testDefaults(): void
    {
        $subject = new RbacGuardOptions();

        $this->assertSame(GuardInterface::POLICY_ALLOW, $subject->getProtectionPolicy());
        $this->assertSame([], $subject->getGuardsProvider());
        $this->assertSame([], $subject->getEventListeners());
        $this->assertSame($subject->getMessagesOptions(), $subject->getMessagesOptions());
    }

    public function testSetsValuesFromConfig(): void
    {
        $subject = new RbacGuardOptions([
            'protection_policy'       => GuardInterface::POLICY_DENY,
            'guards_provider'         => ['type' => 'ArrayGuards'],
            'messages_options'        => ['messages' => [MessagesOptions::FORBIDDEN => 'No access']],
            'event_listeners'         => ['Listener'],
            'guard_manager'           => [],
            'guards_provider_manager' => [],
        ]);

        $this->assertSame(GuardInterface::POLICY_DENY, $subject->getProtectionPolicy());
        $this->assertSame(['type' => 'ArrayGuards'], $subject->getGuardsProvider());
        $this->assertSame(['Listener'], $subject->getEventListeners());
        $this->assertSame('No access', $subject->getMessagesOptions()->getMessage(MessagesOptions::FORBIDDEN));
    }

    public function testRejectsInvalidProtectionPolicy(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Protection policy must be "allow" or "deny", "Allow" given');
        new RbacGuardOptions(['protection_policy' => 'Allow']);
    }
}
