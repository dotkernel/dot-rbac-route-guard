<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard;

use Dot\Rbac\Route\Guard\Exception\InvalidArgumentException;
use Dot\Rbac\Route\Guard\Guard\GuardInterface;

use function in_array;
use function sprintf;

trait ProtectionPolicyTrait
{
    protected string $protectionPolicy = GuardInterface::POLICY_DENY;

    public function getProtectionPolicy(): string
    {
        return $this->protectionPolicy;
    }

    public function setProtectionPolicy(string $protectionPolicy): void
    {
        if (! in_array($protectionPolicy, [GuardInterface::POLICY_ALLOW, GuardInterface::POLICY_DENY], true)) {
            throw new InvalidArgumentException(sprintf(
                'Protection policy must be "%s" or "%s", "%s" given',
                GuardInterface::POLICY_ALLOW,
                GuardInterface::POLICY_DENY,
                $protectionPolicy
            ));
        }

        $this->protectionPolicy = $protectionPolicy;
    }
}
