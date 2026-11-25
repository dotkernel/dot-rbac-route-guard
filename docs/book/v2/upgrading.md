# Upgrading from 0.x to 2.0

There is no 1.x release. Version 2.0.0 follows 0.2.0.

## Changes that can break your code

| Change | Symptom | Fix |
| --- | --- | --- |
| PHP 8.2 is not supported | Composer does not install the package | Use PHP 8.3 or newer |
| Under the `deny` policy, every guard must grant access | A route that passed before now returns 403 | List the route in every guard, or set `protection_policy => allow` in the `options` of the guard that must ignore it |
| A route rule without values denies access in `RoutePermissionGuard` | 403 on a route listed as `'route'` or `'route' => []` | Use `['*']` to allow everyone |
| An invalid `protection_policy` causes an exception | `InvalidArgumentException` when the options or a guard are created | Use `allow` or `deny` |
| `ForbiddenHandler` handles only `ForbiddenException` | An exception with code 403 of another class goes to the error handler | Throw `Dot\Authorization\Exception\ForbiddenException` |
| `dispatchEvent()` returns `AuthorizationEvent\|ResponseInterface` | Fatal error in a class that overrides the method | Change the return type of the override |
| Service getters are not nullable | Fatal error in a subclass that returns `?Type` | Remove the `?` from the return type |
| The plugin managers do not extend `Laminas\ServiceManager\AbstractPluginManager` | `invokables`, `delegators`, `abstract_factories` and other keys in `guard_manager` have no effect | Register a factory for each guard. See "Replacing plugin manager features" |
| An unregistered guard class name in `type` is not created | `InvalidArgumentException: No factory is registered for plugin` | Register a factory for the class |
| The plugin managers throw the exceptions of this package | Code that catches `Laminas\ServiceManager\Exception\InvalidServiceException` does not catch them | Catch `Dot\Rbac\Route\Guard\Exception\ExceptionInterface` |
| `ExceptionInterface` extends `Throwable` | A class that implements it without extending an exception class causes a fatal error | Extend an exception class |
| The package does not require `laminas/laminas-servicemanager` | Class not found errors if your application uses it but does not require it | Run `composer require laminas/laminas-servicemanager` |

## Replacing plugin manager features

In 0.x, `guard_manager` and `guards_provider_manager` accepted all Laminas Service Manager keys.
In 2.0, only `factories` and `aliases` are read.

Before:

```php
'guard_manager' => [
    'invokables' => [
        App\Guard\IpGuard::class => App\Guard\IpGuard::class,
    ],
],
```

After:

```php
'guard_manager' => [
    'factories' => [
        App\Guard\IpGuard::class => static fn ($container, string $name, ?array $options) => new App\Guard\IpGuard($options ?? []),
    ],
],
```

A factory can be a callable or the name of an invokable class.
It gets the container, the resolved guard name and the guard options, in this order.
