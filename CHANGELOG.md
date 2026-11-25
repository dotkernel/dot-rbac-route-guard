# Changelog

## 2.0.0 - TBD

### Added

- Support for PHP 8.6.
- `Dot\Rbac\Route\Guard\Plugin\AbstractPluginManager`, a plugin manager that works with any PSR-11 container.
- `psr/container`, `psr/http-server-handler`, `laminas/laminas-eventmanager` and `dotkernel/dot-authorization` as direct requirements.
- Documentation for version 2, with an upgrade guide and a page about the authorization events.
- Tests for `RbacGuardMiddleware`, `ForbiddenHandler` and their factories. The tests use fixtures instead of mocks.

### Changed

- The package works with any PSR-11 container. `GuardPluginManager` and `GuardsProviderPluginManager` extend `Dot\Rbac\Route\Guard\Plugin\AbstractPluginManager`. The `guard_manager` and `guards_provider_manager` config keys read only `factories` and `aliases`.
- A request continues only when every guard grants access, also under the `deny` policy. When no guards are configured, the global `protection_policy` decides.
- A route rule without values in `RoutePermissionGuard` denies access, as it does in `RouteGuard`.
- The `condition` of a permission rule ignores case.
- `RbacGuardOptions`, `RouteGuard` and `RoutePermissionGuard` throw an `InvalidArgumentException` for a protection policy that is not `allow` or `deny`.
- `ForbiddenHandler` handles only `Dot\Authorization\Exception\ForbiddenException`. Without a template renderer, it throws a new `ForbiddenException` with the original exception as the previous exception.
- `DispatchAuthorizationEventTrait::dispatchEvent()` returns `AuthorizationEvent|ResponseInterface`.
- `RouteGuard::getRoleService()`, `RoutePermissionGuard::getAuthorizationService()`, `AbstractGuardsProvider::getGuardFactory()` and `RbacGuardOptions::getMessagesOptions()` do not return `null`.
- The factories return the type that they create instead of `mixed`.
- `AbstractGuard::setRules()` is not abstract. Both guards share it.
- `Dot\Rbac\Route\Guard\Exception\ExceptionInterface` extends `Throwable`.
- The plugin managers throw `Dot\Rbac\Route\Guard\Exception\InvalidArgumentException` and `RuntimeException` instead of `Laminas\ServiceManager\Exception\InvalidServiceException`.
- PHPUnit 12, PHPStan at level 8, and PHPUnit fails on notices, warnings, deprecations and risky tests.
- The CI actions: `actions/checkout@v7`, `actions/cache@v6` and `codecov/codecov-action@v7`.

### Deprecated

- Nothing.

### Removed

- Support for PHP 8.2.
- The `laminas/laminas-servicemanager` requirement. Applications that use it must require it directly.
- The Laminas Service Manager keys `invokables`, `services`, `abstract_factories`, `delegators`, `initializers`, `shared` and `lazy_services` in `guard_manager` and `guards_provider_manager`.
- `ForbiddenHandler` no longer handles other exceptions with the code 403, and the protected property `$authorizationStatusCodes` is removed.

### Fixed

- A listener that returned a response caused a `TypeError`.
- `ForbiddenHandlerFactory` raised a warning when the `debug` key was not set.
- `RbacGuardOptionsFactory` raised a warning when the `dot_authorization` key was not set.
- The `*` wildcard in a rule with `permissions` and `condition` had no effect.
- The message for an invalid `condition` showed the type instead of the value.
- Wrong class names and an Expressive 2 pipeline example in the documentation.

### Security

- Under the `deny` policy, a guard that granted access caused the other guards to be skipped.

## 0.2.0 - 2025-11-03

### Added

- Support for PHP 8.4 and 8.5.

### Removed

- Support for PHP 8.1.

## 0.1.0 - 2025-02-27

### Added

- Initial release.
