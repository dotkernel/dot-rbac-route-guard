# Configuration

Create the file `config/autoload/authorization-guards.global.php`.
You can copy `authorization-guards.global.php.dist` from the root of the package.

## authorization-guards.global.php

```php
<?php

declare(strict_types=1);

use Dot\Rbac\Route\Guard\Guard\GuardInterface;
use Dot\Rbac\Route\Guard\Options\MessagesOptions;

return [
    'dot_authorization' => [
        // The policy for routes that a guard does not list: allow or deny. The default is allow.
        'protection_policy' => GuardInterface::POLICY_ALLOW,

        // Listeners for the authorization events. See the "Authorization events" page.
        'event_listeners' => [
            // [
            //     'type'     => App\Listener\AuthorizationListener::class,
            //     'priority' => 1,
            // ],
        ],

        // Register custom guards here. Only "factories" and "aliases" are read.
        'guard_manager' => [],

        // Register custom guards providers here. Only "factories" and "aliases" are read.
        'guards_provider_manager' => [],

        // The guards provider and its configuration.
        'guards_provider' => [
            'type'    => 'ArrayGuards',
            'options' => [
                'guards' => [
                    [
                        'type'    => 'Route',
                        'options' => [
                            'rules' => [
                                'premium' => ['admin'],
                                'login'   => ['guest'],
                                'logout'  => ['admin', 'user', 'viewer'],
                                'account' => ['admin', 'user'],
                                'home'    => ['*'],
                            ],
                        ],
                    ],
                    [
                        'type'    => 'RoutePermission',
                        'options' => [
                            // This guard ignores routes that it does not list, also when the global policy is deny.
                            'protection_policy' => GuardInterface::POLICY_ALLOW,
                            'rules'             => [
                                'premium' => ['premium'],
                                'account' => [
                                    'permissions' => ['my-account', 'admin-account'],
                                    'condition'   => GuardInterface::CONDITION_OR,
                                ],
                                'logout'  => ['only-logged'],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        // Replace the default messages.
        'messages_options' => [
            'messages' => [
                // MessagesOptions::UNAUTHORIZED => 'You must sign in first to access the requested content',
                // MessagesOptions::FORBIDDEN    => 'You don\'t have enough permissions to access the requested content',
            ],
        ],
    ],
];
```

## How the middleware decides

`RbacGuardMiddleware` asks the guards in the order of their priority. `RouteGuard` has priority 100 and `RoutePermissionGuard` has priority 70.

- The request continues only when every guard grants access.
- The middleware stops at the first guard that denies access.
- Each guard applies its own protection policy to a route that it does not list.
- When no guards are configured, the global `protection_policy` decides.
- A request without a `RouteResult` continues, so that the "not found" handler can process it.

When access is denied, the middleware throws an exception:

| Situation | Exception | Code |
| --- | --- | --- |
| A guest, when an `AuthenticationInterface` service is registered | `Dot\Authentication\Exception\UnauthorizedException` | 401 |
| An identity, or no authentication service | `Dot\Authorization\Exception\ForbiddenException` | 403 |

## Protection policy

The value must be `allow` or `deny`. Use the constants `GuardInterface::POLICY_ALLOW` and `GuardInterface::POLICY_DENY`.
Another value, for example `Allow`, causes an `InvalidArgumentException`.

The global `protection_policy` is the default for each guard.
Set `protection_policy` in the `options` of a guard to change it for that guard only.

## Route rules

The route name is the key of a rule. The match ignores the case of the route name.

| Rule | RouteGuard | RoutePermissionGuard |
| --- | --- | --- |
| `'home' => ['*']` | Everyone has access | Everyone has access |
| `'account' => ['admin', 'user']` | One of the roles is necessary | All permissions are necessary |
| `'account' => ['permissions' => [...], 'condition' => 'OR']` | Not used | One permission is necessary |
| `'account' => ['permissions' => [...], 'condition' => 'AND']` | Not used | All permissions are necessary |
| `'account'` (no key, no value) | Nobody has access | Nobody has access |
| `'account' => []` | Nobody has access | Nobody has access |

The `condition` value is `AND` or `OR`. The match ignores case. Another value causes an `InvalidArgumentException`.

## Custom guards

A custom guard implements `Dot\Rbac\Route\Guard\Guard\GuardInterface`.
Register a factory for it in `guard_manager`. A factory gets the container, the guard name and the guard options:

```php
'guard_manager' => [
    'factories' => [
        App\Guard\IpGuard::class => App\Guard\IpGuardFactory::class,
    ],
    'aliases'   => [
        'Ip' => App\Guard\IpGuard::class,
    ],
],
```

```php
<?php

declare(strict_types=1);

namespace App\Guard;

use Psr\Container\ContainerInterface;

class IpGuardFactory
{
    /**
     * @param array<array-key, mixed>|null $options
     */
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): IpGuard
    {
        return new IpGuard($options ?? []);
    }
}
```

Then use `'type' => 'Ip'` in the `guards` list.
To reuse the role or permission logic, extend `RouteGuard` or `RoutePermissionGuard`, and register `GuardFactory` or `PermissionGuardFactory` as the factory.
