<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Guard;

use Dot\Rbac\Route\Guard\Exception\InvalidArgumentException;
use Dot\Rbac\Route\Guard\ProtectionPolicyTrait;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;

use function is_array;
use function is_int;
use function is_string;
use function strtolower;

abstract class AbstractGuard implements GuardInterface
{
    use ProtectionPolicyTrait;

    public const PRIORITY = 1;

    /** @var array<string, array<array-key, mixed>> */
    protected array $rules = [];

    /**
     * @param array<array-key, mixed> $options
     */
    public function __construct(array $options = [])
    {
        if (isset($options['rules']) && is_array($options['rules'])) {
            $this->setRules($options['rules']);
        }

        if (isset($options['protection_policy'])) {
            if (! is_string($options['protection_policy'])) {
                throw new InvalidArgumentException('Protection policy must be a string');
            }
            $this->setProtectionPolicy($options['protection_policy']);
        }
    }

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Stores the rules by lower-case route name.
     *
     * A rule with an integer key lists a route without values, which denies access to it.
     *
     * @param array<array-key, mixed> $rules
     */
    public function setRules(array $rules): void
    {
        $this->rules = [];

        foreach ($rules as $key => $value) {
            if (is_int($key)) {
                if (! is_string($value)) {
                    continue;
                }
                $this->rules[strtolower($value)] = [];
                continue;
            }

            $this->rules[strtolower($key)] = (array) $value;
        }
    }

    public function getPriority(): int
    {
        return static::PRIORITY;
    }

    /**
     * Returns the rule of the matched route, or null when the request has no matched route or the route has no rule.
     *
     * @return array<array-key, mixed>|null
     */
    protected function getMatchedRule(ServerRequestInterface $request): ?array
    {
        $routeResult = $request->getAttribute(RouteResult::class);
        if (! $routeResult instanceof RouteResult) {
            return null;
        }

        $routeName = $routeResult->getMatchedRouteName();
        if ($routeName === false || $routeName === '') {
            return null;
        }

        return $this->rules[strtolower($routeName)] ?? null;
    }
}
