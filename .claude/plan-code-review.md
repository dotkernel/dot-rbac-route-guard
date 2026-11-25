# Plan: dotkernel/dot-rbac-route-guard 2.0.0

This plan prepares release `2.0.0` of `dotkernel/dot-rbac-route-guard` on the release branch `2.0`.
The work happens on the current local branch `upgrade-major`. The remote also has a branch `upgrade` (see V7).
The plan was written on 2026-11-25 with PHP 8.5.13, from the state of the working tree at commit `f23db79` (tag `0.2.0`) plus the maintainer's uncommitted edits.
No file other than this plan was changed.

## Implementation status

Updated on 2026-11-25 after the implementation on branch `upgrade-major`. `composer check` passes: phpcs clean, 120 tests and 227 assertions, PHPStan level 8 with 0 errors.

| Item | State | Notes |
| --- | --- | --- |
| V1 PHP versions in `composer.json` | Done by maintainer | `~8.3.0 \|\| ~8.4.0 \|\| ~8.5.0 \|\| ~8.6.0` |
| V2 Workflow PHP matrix and action versions | Done | Platform flag for PHP 8.6 added to `codecov.yml` and `static-analysis.yml`. Job key `mutation` renamed to `static-analysis` |
| V3 `phpstan.neon` level 8 | Done | 0 errors in `src` and `test`. No ignore, baseline, `assert()` or `@var` override |
| V4 PHPUnit 12 | Done | `phpunit.xml` uses the vendor schema and the `failOn*` flags. `.phpunit.cache` is in `.gitignore` |
| V5 Undeclared and unused dependencies | Done by maintainer | `composer.json` edited and `composer update` run. Checks re-run, all pass |
| V6 Remove `laminas/laminas-servicemanager` | Done | Removed from `require`. It is still installed through `dot-event` 4.4.0 and `dot-rbac` 4.2.1 (`composer why`) until their 5.0.0 releases. |
| V7 Remote branch `upgrade` | Not used | The maintainer chose `upgrade-major`. The tests were rewritten, so the test changes of `origin/upgrade` are not necessary |
| V8 Backward-compatibility check in CI | Open | Kept as `true`. Expect a failing BC job until `2.0.0` is tagged |
| F1 to F10 | Done | With tests. Each reproduction in `repro.php` now shows the fixed result |
| F11 Tests | Done | Fixtures replace all mocks. Coverage not measured, no driver installed |
| Docs `docs/book/v2` | Done | Six pages. `mkdocs.yml`, `README.md`, `SECURITY.md` and the `.dist` config updated |
| CHANGELOG | Done | `2.0.0 - TBD`, `0.2.0` and `0.1.0` |

Files added in `src`: `Plugin/AbstractPluginManager.php`, `Factory/GuardOptionsTrait.php` and `Factory/PluginManagerConfigTrait.php`.
In `test`, fixtures in `test/Fixture` replace all mocks. Some test files were merged or renamed, for example `Guard/FactoryTest.php` became `Guard/GuardFactoryTest.php`.

Decisions taken during the implementation:

- `Plugin/AbstractPluginManager` is a copy of `Dot\Rbac\Plugin\AbstractPluginManager` from the `dot-rbac` 5.0.0 branch. The installed `dot-rbac` 4.2.1 does not have that class. When `dot-rbac` 5 is the minimum, this package can extend the `dot-rbac` class and delete its own copy.
- With no guards configured, the global `protection_policy` decides (as in 0.x). With guards, every guard must grant access (F2).
- A guard `protection_policy` that is set but invalid now throws. In 0.x the global policy silently replaced it.
- Listeners registered in `event_listeners` cannot return a response, because the interface methods return `void`. The docs say so. A change of the interface to `?ResponseInterface` would break every listener, so I did not make it. Decide if you want it in 2.0.
- The `ForbiddenHandler` constructor keeps the parameter name `$templateRenderer`, so calls with named arguments still work.
- `codecov.yml` still uploads coverage from four PHP versions. I did not change it.

## Decision: version 2.0.0

- The tags are `0.1.0` (2025-02-27) and `0.2.0` (2025-11-03). No `1.x` tag exists on the remote.
- The docs folder is `docs/book/v1`, and `README.md` and `SECURITY.md` show a `1.x` line. The published releases are `0.x`. This is a mismatch in the current docs.
- The maintainer chose `2.0.0` on the branch `2.0`. Semantic versioning permits this. Version `1.0.0` will not exist.
- I am not certain why the number `1` is skipped. Please confirm that `2.0.0` is intentional. A short note in the upgrade guide ("there is no 1.x release") prevents questions from users.
- No other release line exists (only `main`, `gh-pages` and `upgrade` on the remote). The upgrade guide covers one path: `0.x` to `2.0`.
- `2.0.0` must be a major release, because F2, F7 and V6 change public behaviour and public class hierarchies.
- The package `dotkernel/dot-rbac-guard` is a separate package with a similar design. The `conflict` entry in `composer.json` blocks both in one application. The Dotkernel Admin and Frontend applications use `dot-rbac-guard`, not this package.

## Baseline

Commands ran on 2026-11-25 in `/var/www/html/dotkernel/dot-rbac-route-guard`.

| Check | Result |
| --- | --- |
| PHP versions available locally | 8.5.13 only. PHP 8.3, 8.4 and 8.6 are not installed |
| Coverage driver | None (no Xdebug, no PCOV) |
| `git describe --tags` | `0.2.0` (HEAD is the tag) |
| `composer check` | Fails at the `static-analysis` step |
| `phpcs` | Pass, 53 files |
| `phpunit` | 42 tests, 110 assertions, pass, 9 tests with 10 PHPUnit notices |
| `phpstan` level 5 / 6 / 7 / 8 | 0 / 75 / 88 / 92 errors |
| Installed PHPUnit | 12.5.45 |
| Installed PHPStan | 2.3.7 |

PHPStan error identifiers at level 8:

| Identifier | Count |
| --- | --- |
| `missingType.iterableValue` | 46 |
| `missingType.generics` | 26 |
| `method.notFound` | 8 |
| `method.nonObject` | 7 |
| `argument.type` | 5 |

Not run:

- Tests on PHP 8.3, 8.4 and 8.6. These versions are not installed.
- Coverage. No coverage driver is installed.
- Lowest dependencies (`composer update --prefer-lowest`). The guard hooks block installs.
- The Laminas backward-compatibility check (Roave). It runs only in the Laminas CI workflow. I am not certain which tag it compares against. It probably compares against the most recent tag, `0.2.0`. You may want to verify this in the CI log.

## Done by the maintainer

Edits already in the working tree:

- `composer.json`: PHP `~8.3.0 || ~8.4.0 || ~8.5.0 || ~8.6.0`, `phpunit/phpunit` `^12.5.22`.
- `phpstan.neon`: `level: 8`.
- `.github/workflows/codecov.yml` and `static-analysis.yml`: PHP 8.2 removed, PHP 8.6 added, `actions/checkout@v7`, `actions/cache@v6`, `codecov/codecov-action@v7`.
- `.laminas-ci.json`: `ignore_php_platform_requirements` for 8.6.

Edits still needed. The guard hooks deny edits to `composer.json` by Claude, so the maintainer must make them.

```diff
-    "description": "Dotkernel RBAC Route guard component",
+    "description": "Dotkernel RBAC route guard component for Mezzio, for any PSR-11 container",
+    "support": {
+        "docs": "https://docs.dotkernel.org/dot-rbac-route-guard/",
+        "issues": "https://github.com/dotkernel/dot-rbac-route-guard/issues"
+    },
     "require": {
         "php": "~8.3.0 || ~8.4.0 || ~8.5.0 || ~8.6.0",
-        "dotkernel/dot-authentication": "^2.3.4",
-        "dotkernel/dot-event": "^4.2",
-        "dotkernel/dot-rbac": "^4.0",
+        "dotkernel/dot-authentication": "^2.3.4 || ^3.0",
+        "dotkernel/dot-authorization": "^3.6 || ^4.0",
+        "dotkernel/dot-event": "^4.2 || ^5.0",
+        "dotkernel/dot-rbac": "^4.0 || ^5.0",
         "laminas/laminas-diactoros": "^3.3.0",
-        "laminas/laminas-servicemanager": "^4.0",
+        "laminas/laminas-eventmanager": "^3.16",
         "laminas/laminas-stdlib": "^3.19",
         "mezzio/mezzio-router": "^3.17",
         "mezzio/mezzio-template": "^2.10.0",
+        "psr/container": "^1.1 || ^2.0",
         "psr/http-message": "^1.0 || ^2.0",
+        "psr/http-server-handler": "^1.0",
         "psr/http-server-middleware": "^1.0"
     },
```

Why the wide ranges for the four `dotkernel/*` packages:

- The code works with both lines. The test suite passes with the installed versions (4.x, 3.x, 2.x), and with the sibling branches `dot-event` 5.0.0, `dot-rbac` 5.0.0 (TBD), `dot-authorization` 4.0.0 (TBD) and `dot-authentication` 3.0.0 (TBD), loaded from `../` by a scratch autoloader. That autoloader also throws if any `Laminas\ServiceManager` class is loaded.
- `dot-event` 4.x and `dot-rbac` 4.x still require `laminas/laminas-servicemanager`. To remove it from user installs, tighten the ranges to `^5.0` (and `^4.0`, `^3.0`) before you tag `2.0.0`, after those packages are released.
- `composer update` with the new majors will fail until they are released on Packagist, or until you add path repositories.

Then run `composer update` and tell me, so I can re-run the checks.

## Findings

### V1. PHP versions of the dependencies do not include 8.6

- Location: `composer.json`, dependencies in `vendor/`.
- Evidence: I read the `require.php` of each installed package from `vendor/*/composer.json`.

| Package | Installed | PHP constraint |
| --- | --- | --- |
| `dotkernel/dot-event` | 4.4.0 | `~8.2.0 \|\| ~8.3.0 \|\| ~8.4.0 \|\| ~8.5.0` |
| `dotkernel/dot-rbac` | 4.2.1 | `~8.2.0 \|\| ~8.3.0 \|\| ~8.4.0 \|\| ~8.5.0` |
| `dotkernel/dot-authorization` | 3.8.1 | `~8.2.0 \|\| ~8.3.0 \|\| ~8.4.0 \|\| ~8.5.0` |
| `dotkernel/dot-authentication` | 2.7.0 | `~8.2.0 \|\| ~8.3.0 \|\| ~8.4.0 \|\| ~8.5.0` |
| `mezzio/mezzio-router` | 3.20.0 | `~8.2.0 \|\| ~8.3.0 \|\| ~8.4.0 \|\| ~8.5.0` |
| `mezzio/mezzio-template` | 2.13.0 | `~8.2.0 \|\| ~8.3.0 \|\| ~8.4.0 \|\| ~8.5.0` |
| `laminas/laminas-servicemanager` | 4.6.0 | includes `~8.6.0` |
| `laminas/laminas-eventmanager` | 3.16.0 | includes `~8.6.0` |
| `laminas/laminas-diactoros` | 3.10.0 | includes `~8.6.0` |
| `laminas/laminas-stdlib` | 3.22.0 | includes `~8.6.0` |

- Before: `composer.json` allows PHP 8.6, but six dependencies do not.
- After: no change to `composer.json`. Release `2.0.0` only after newer releases of the four `dotkernel/*` packages allow PHP 8.6, or accept that users on PHP 8.6 need `--ignore-platform-req=php`.
- Why: a PHP 8.6 user cannot install the package without that flag. I did not check Packagist for newer releases. You may want to verify this on the published package pages.

### V2. PHP 8.6 jobs in two workflows cannot install dependencies

- Location: `.github/workflows/codecov.yml:46`, `.github/workflows/static-analysis.yml:47`.
- Evidence: V1. The step runs `composer install` with no platform flag. `.laminas-ci.json` covers only the Laminas CI workflow, not these two files.
- Before:

```yaml
      - name: Install dependencies with composer
        run: composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader --ansi
```

- After (in both files):

```yaml
      - name: Install dependencies with composer
        run: composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader --ansi ${{ matrix.php == '8.6' && '--ignore-platform-req=php' || '' }}
```

- Why: without the flag, the PHP 8.6 jobs fail before the tests start. Remove the flag when the dependencies support PHP 8.6.
- Also recommended:
    - `codecov.yml` uploads coverage four times. Upload from one PHP version only, or keep it and accept the duplicate uploads.
    - In `static-analysis.yml`, the job key is `mutation`. Rename it to `static-analysis`. The `coverage: pcov` line is not necessary for PHPStan.
    - `continuous-integration.yml` and `docs-build.yml` need no version change.
    - I did not verify that `shivammathur/setup-php@v2` provides PHP 8.6 today. Check it in the first CI run.

### V3. PHPStan level 8

- Location: `phpstan.neon`, `src/`, `test/`.
- Evidence: `vendor/bin/phpstan analyse --level=8` reports 92 errors. The full list is in Baseline. No ignore, baseline or `assert()` is proposed.
- Fixes by group:

| Group | Count | Fix |
| --- | --- | --- |
| `missingType.iterableValue` in `src/Options/*`, `src/Guard/*`, `src/Provider/*`, factories, `ConfigProvider` | 39 | Add value types, for example `array<string, mixed>` for options, `array<string, list<string>>` for route rules, `list<array{type: string, options?: array<string, mixed>}>` for guard specs, `array<int, string>` for messages |
| `missingType.iterableValue` in `test/` | 7 | Add `array<string, mixed>` to fixture properties |
| `missingType.generics` on `AuthorizationEvent` | 15 | Fix the template types once on the class, see F1 |
| `missingType.generics` on `ResponseCollection` | 3 | Removed by F1, the return type no longer uses `ResponseCollection` |
| `missingType.generics` on the plugin managers | 8 | Add `@extends AbstractPluginManager<GuardInterface>` and `@extends AbstractPluginManager<GuardsProviderInterface>`, and remove `@template T`. If V6 is accepted, these errors disappear |
| `method.notFound` and `argument.type` in the middleware | 7 | Removed by F1 |
| `method.nonObject` on nullable services | 5 | F10 |
| `method.nonObject` on `getMessagesOptions()` | 2 | F10 |
| Test mocks typed `X\|MockObject` | 5 | Use the intersection type `ContainerInterface&MockObject`, or use a stub or a fixture (F11) |

- Before:

```php
/**
 * @template TTarget of object|string|null
 * @template TParams of array|ArrayAccess|object
 * @extends Event<TTarget, TParams>
 */
class AuthorizationEvent extends Event
```

- After:

```php
/**
 * @extends Event<object|string|null, array<string, mixed>>
 */
class AuthorizationEvent extends Event
```

- Why: the event is always created with an array of parameters. One concrete type removes 15 errors and makes `getParams()` return an array. The change affects docblocks only.

### V4. PHPUnit 12

- Location: `phpunit.xml`, `test/`.
- Evidence: `phpunit.xml` declares schema `10.2`. Nine tests trigger PHPUnit notices "No expectations were configured for the mock object". `--fail-on-phpunit-notice` gives exit code 1. No test uses annotations or data providers, so no attribute migration is needed.
- The maintainer already changed `composer.json`. PHPUnit 12.5.45 is installed.
- Before:

```xml
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.2/phpunit.xsd" bootstrap="./vendor/autoload.php" colors="true">
```

- After (all attribute names read from `vendor/phpunit/phpunit/phpunit.xsd`):

```xml
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="./vendor/autoload.php"
         colors="true"
         cacheDirectory=".phpunit.cache"
         displayDetailsOnAllIssues="true"
         failOnDeprecation="true"
         failOnNotice="true"
         failOnWarning="true"
         failOnRisky="true"
         failOnPhpunitDeprecation="true"
         failOnPhpunitNotice="true"
         failOnPhpunitWarning="true"
         failOnEmptyTestSuite="true"
         beStrictAboutOutputDuringTests="true">
```

- Test changes: replace `createMock()` with `createStub()` where the test sets no expectation (the nine tests in the notices). Add `.phpunit.cache` to `.gitignore`.
- Why: the flags turn hidden problems into failures. Deprecations raised in `vendor/` code on PHP 8.6 can also fail the run. If that happens, add `<source ignoreSuppressionOfDeprecations="false">` handling, or limit the deprecation report to `src`. I could not test this, because PHP 8.6 is not installed.

### V5. Undeclared and unused dependencies

- Location: `composer.json`, `src/`.
- Evidence: I listed each `use` statement in `src/` and compared it with `require`.

| Package | Used in `src/` | Declared | Action |
| --- | --- | --- | --- |
| `dotkernel/dot-authorization` | `AuthorizationInterface`, `ForbiddenException` (8 files) | No, comes from `dot-rbac` | Add `^3.6` (same as `dot-rbac` requires) |
| `laminas/laminas-eventmanager` | 6 classes and traits | No, comes from `dot-event` | Add `^3.16` (installed version, lower bound unverified) |
| `psr/container` | 13 files | No, comes from `laminas-servicemanager` | Add `^1.1 \|\| ^2.0` |
| `psr/http-server-handler` | `RequestHandlerInterface` | No, comes from `psr/http-server-middleware` | Add `^1.0` |
| `dotkernel/dot-event` | `Dot\Event\Event` only | Yes | Keep. See V6 for its transitive `laminas-servicemanager` |
| `laminas/laminas-diactoros` | `Response`, `HtmlResponse` in `ForbiddenHandler` | Yes | Keep for 2.0. See "Optional: diactoros" below |
| `mezzio/mezzio-template` | `TemplateRendererInterface`, optional at run time | Yes | Keep |
| `laminas/laminas-stdlib` | `AbstractOptions`, `ArrayUtils` | Yes | Keep |

- No declared runtime package is unused.
- Optional: diactoros. `laminas/laminas-diactoros` exists for two classes. A PSR-17 `ResponseFactoryInterface` and `StreamFactoryInterface` can replace it. The diactoros `ConfigProvider` already registers both interfaces (read in `vendor/laminas/laminas-diactoros/src/ConfigProvider.php`). This adds a constructor parameter to `ForbiddenHandler`, which is a break. I recommend that you keep diactoros in 2.0 and do this in a later major, because the gain is small.

### V6. Remove `laminas/laminas-servicemanager` from `require`

- Location: `src/Guard/GuardPluginManager.php`, `src/Provider/GuardsProviderPluginManager.php`, `src/Guard/Factory.php`, `src/Provider/Factory.php`, factories, tests.
- Evidence: `grep -rni "ServiceManager" src test` finds `AbstractPluginManager` and `InvalidServiceException` in the two plugin managers, and plugin-manager mocks in 5 test files. I read `dot-dependency-injection` from `../dot-dependency-injection` (its `composer.json` requires only `php` and `psr/container`, and its `ConfigProvider` returns `[]`).
- Important limit: `dotkernel/dot-event` 4.4.0 and `dotkernel/dot-rbac` 4.2.1 both require `laminas/laminas-servicemanager`. The package stays installed through them. The gain of V6 is that this package's own code no longer depends on it. Users do not get a smaller install.
- Different case from `dot-dependency-injection`: there the Laminas part was an optional abstract factory. Here the plugin managers are the core of the guard configuration (`guard_manager` and `guards_provider_manager`).

Options:

| Option | Effect | Break |
| --- | --- | --- |
| A. Replace both plugin managers with small PSR-11 registries (recommended if you want V6) | Code depends on `psr/container` only | The classes no longer extend `AbstractPluginManager`. The `guard_manager` config keeps `factories` and `aliases` only. `invokables`, `abstract_factories`, `delegators`, `initializers`, `services`, `shared` and `lazy_services` stop working |
| B. Keep `laminas-servicemanager` in 2.0 | No code change | None |

The first recommendation was option B. The maintainer then chose option A, because `dot-event` 5.0.0 and `dot-rbac` 5.0.0 also remove the dependency. Option A is implemented. The final code extends `Dot\Rbac\Route\Guard\Plugin\AbstractPluginManager`, which is the same design as `dot-rbac` 5.0.0.

Option A, before:

```php
class GuardPluginManager extends AbstractPluginManager
{
    protected string $instanceOf = GuardInterface::class;

    protected array $factories = [
        RouteGuard::class           => GuardFactory::class,
        RoutePermissionGuard::class => PermissionGuardFactory::class,
    ];

    protected array $aliases = [ /* 11 aliases */ ];

    public function validate(mixed $instance): void { /* throws InvalidServiceException */ }
}
```

Option A, after:

```php
class GuardPluginManager
{
    /** @var array<string, class-string|callable> */
    private array $factories = [
        RouteGuard::class           => GuardFactory::class,
        RoutePermissionGuard::class => PermissionGuardFactory::class,
    ];

    /** @var array<string, string> */
    private array $aliases = [ /* the same 11 aliases */ ];

    /**
     * @param array{factories?: array<string, class-string|callable>, aliases?: array<string, string>} $config
     */
    public function __construct(private readonly ContainerInterface $container, array $config = [])
    {
        $this->factories = array_merge($this->factories, $config['factories'] ?? []);
        $this->aliases   = array_merge($this->aliases, $config['aliases'] ?? []);
    }

    public function has(string $name): bool
    {
        return isset($this->factories[$this->aliases[$name] ?? $name]);
    }

    /**
     * @param array<string, mixed>|null $options
     */
    public function build(string $name, ?array $options = null): GuardInterface
    {
        $name    = $this->aliases[$name] ?? $name;
        $factory = $this->factories[$name] ?? null;
        if ($factory === null) {
            throw new RuntimeException(sprintf('Guard "%s" is not registered', $name));
        }
        if (is_string($factory)) {
            $factory = new $factory();
        }
        $guard = $factory($this->container, $name, $options);
        if (! $guard instanceof GuardInterface) {
            throw new RuntimeException(sprintf('Guard "%s" must implement %s', $name, GuardInterface::class));
        }

        return $guard;
    }
}
```

- `GuardsProviderPluginManager` gets the same shape with `GuardsProviderInterface`.
- Current factories already have the signature `__invoke(ContainerInterface $container, string $requestedName, ?array $options = null)`. They need no change.
- `ConfigProvider` keeps the `dependencies` block. It registers only factories, which every PSR-11 container that accepts a factory per key can use.
- Tests: replace `ServiceManager` and plugin-manager mocks with a fixture container (`test/Fixture/InMemoryContainer.php` with `get(string $id): mixed` and `has(string $id): bool`) and fixture exceptions `test/Fixture/NotFoundException.php` and `test/Fixture/ContainerException.php` that implement `NotFoundExceptionInterface` and `ContainerExceptionInterface`.
- CHANGELOG: list the removed base class and the removed config keys under "Removed". The upgrade guide gets a "Replacing plugin manager features" section.
- Verify: `grep -rn "Laminas\\\\ServiceManager" src test` returns nothing, and `composer check` passes. The lowest `psr/container` version stays unverified unless it is installed.

### V7. The remote branch `upgrade` overlaps this work

- Evidence: `git diff 0.2.0 origin/upgrade --stat` shows 8 files. It has the same `composer.json` edits, the PHPUnit 12.5 schema, and `createMock()` to `createStub()` changes in 4 test files.
- You asked me to stay on branch `upgrade`, but the current local branch is `upgrade-major`. I did not switch branches. Please confirm which branch to use.
- Recommendation: take the test changes from `origin/upgrade` (for example with `git cherry-pick 3d44c85` or a merge), then continue on one branch only.

### V8. Backward-compatibility check in Laminas CI

- Location: `.laminas-ci.json`.
- Evidence: `"backwardCompatibilityCheck": true`. Release 2.0.0 has intended breaks (F2, F7, F10, V6).
- Before:

```json
{
  "ignore_php_platform_requirements": {
    "8.6": true
  },
  "backwardCompatibilityCheck": true
}
```

- After: keep the check, and accept the expected failure on the `2.0` branch until `2.0.0` is tagged. Or set it to `false` on the `2.0` branch and back to `true` after the tag.
- Why: a red BC job on every pull request hides new failures. This is unverified, because the check runs only in CI.

### F1. A listener that returns a response causes a `TypeError` (High)

- Location: `src/Event/DispatchAuthorizationEventTrait.php:15-34`.
- Evidence: scratch script `repro.php`, case F1. A listener on `EVENT_BEFORE_AUTHORIZATION` returns a `TextResponse`. Result: `TypeError: RbacGuardMiddleware::dispatchEvent(): Return value must be of type AuthorizationEvent|ResponseCollection, TextResponse returned`. The short-circuit feature never worked.
- Before:

```php
public function dispatchEvent(
    string $name,
    array $params = [],
    mixed $target = null
): AuthorizationEvent|ResponseCollection {
    // ...
    if ($r->stopped()) {
        return $r->last();
    }

    return $event;
}
```

- After:

```php
/**
 * @param array<string, mixed> $params
 */
public function dispatchEvent(
    string $name,
    array $params = [],
    mixed $target = null
): AuthorizationEvent|ResponseInterface {
    // ...
    if ($r->stopped() && $r->last() instanceof ResponseInterface) {
        return $r->last();
    }

    return $event;
}
```

- Why: callers in `RbacGuardMiddleware` and `ForbiddenHandler` already check `instanceof ResponseInterface`. The return type was wrong. It also removes 10 PHPStan errors. The signature change is a break for code that calls `dispatchEvent()` directly.
- Test: a listener returns a response, and `process()` returns that response for each of the three events.

### F2. With the deny policy, one granting guard skips the other guards (High, security)

- Location: `src/Middleware/RbacGuardMiddleware.php:73-85`.
- Evidence: `repro.php`, case F2. Policy `deny`. `RouteGuard` grants (role `user` is allowed), `RoutePermissionGuard` denies (permission `no` is missing). Result: the request passes to the handler. With policy `allow` and the same rules, the request is denied (case F2b). `dotkernel/dot-rbac-guard` has the same loop.
- Before:

```php
$isGranted = $this->options->getProtectionPolicy() === GuardInterface::POLICY_ALLOW;
foreach ($guards as $guard) {
    // ...
    $r = $guard->isGranted($request);
    if ($r !== $isGranted) {
        $isGranted = $r;
        break;
    }
}
```

- After:

```php
$isGranted = true;
foreach ($guards as $guard) {
    if (! $guard instanceof GuardInterface) {
        throw new RuntimeException('Guard is not an instance of ' . GuardInterface::class);
    }

    if (! $guard->isGranted($request)) {
        $isGranted = false;
        break;
    }
}
```

- Why: under the deny policy, the permission guard has no effect for a route that the role guard grants. Each guard already applies the protection policy to routes it does not know. So "every guard must grant" is correct for both policies. A user who wants a guard to ignore unknown routes under the global deny policy sets `protection_policy => allow` in that guard's `options`. `GuardFactory` and `PermissionGuardFactory` already read this key.
- Decision needed: this changes the result for applications with the deny policy. Routes that only one guard lists become denied. Document it in the upgrade guide.
- Tests: a table of policy (allow, deny) by guard results (grant/grant, grant/deny, deny/grant, deny/deny).

### F3. `ForbiddenHandlerFactory` warns when the config has no `debug` key (Medium)

- Location: `src/Factory/ForbiddenHandlerFactory.php:28`.
- Evidence: `repro.php`, case F3: `Undefined array key "debug"`. The integration check (`integration.php`) shows the warning on each request with the real `ServiceManager` and `dot-rbac`.
- Before:

```php
$debug  = is_bool($config['debug']) && $config['debug'];
```

- After:

```php
$debug  = ($config['debug'] ?? false) === true;
```

- Why: with `failOnWarning`, or an error handler that converts warnings, the factory fails.
- Test: factory with a config without `debug`, with `debug => true`, with `debug => 'yes'`.

### F4. A rule without values means "deny all" in one guard and "allow all" in the other (Medium)

- Location: `src/Guard/RouteGuard.php:42-44`, `src/Guard/RoutePermissionGuard.php:49-51`, `:90-97`.
- Evidence: `repro.php`, case F4. Rules `['premium']` (integer key). `RouteGuard` returns `false`. `RoutePermissionGuard` returns `true`, because an `AND` over an empty list is true.
- Before:

```php
if (GuardInterface::CONDITION_AND === $condition) {
    foreach ($permissions as $permission) {
        // ...
    }
    return true;
}
```

- After:

```php
if ($permissions === []) {
    return false;
}

if (GuardInterface::CONDITION_AND === $condition) {
    // unchanged
}
```

- Why: a listed route with no permissions must not grant access to everyone. This is a behaviour change. Users who want "everyone" use `['*']`.
- Test: both guards with an integer-key rule return `false`.

### F5. The structured permission rule ignores `*` and reports the wrong value (Low)

- Location: `src/Guard/RoutePermissionGuard.php:83-111`.
- Evidence: `repro.php`, case F5. `['premium' => ['permissions' => ['*']]]` returns `false`, but `['premium' => ['*']]` returns `true`. Condition `XOR` and condition `or` give "Condition must be either "AND" or "OR", string given". The message shows the type, not the value. Lower-case `or` is rejected.
- Before:

```php
if (in_array('*', $allowedPermissions)) {
    return true;
}

$permissions = $allowedPermissions['permissions'] ?? $allowedPermissions;
$condition   = $allowedPermissions['condition'] ?? GuardInterface::CONDITION_AND;
```

- After:

```php
$permissions = $allowedPermissions['permissions'] ?? $allowedPermissions;
$condition   = $allowedPermissions['condition'] ?? GuardInterface::CONDITION_AND;
if (in_array('*', $permissions, true)) {
    return true;
}
$condition = is_string($condition) ? strtoupper($condition) : $condition;
// ...
throw new InvalidArgumentException(sprintf(
    'Condition must be either "AND" or "OR", "%s" given',
    is_scalar($condition) ? (string) $condition : get_debug_type($condition)
));
```

- Why: the two rule formats must have the same meaning. Better messages help users fix config.
- Also: use strict `in_array(..., true)` in both guards.
- Test: structured wildcard, lower-case `or`, invalid condition message.

### F6. `RbacGuardOptions` accepts any protection policy (Low)

- Location: `src/Options/RbacGuardOptions.php:39-42`, `src/ProtectionPolicyTrait.php:18-21`.
- Evidence: `repro.php`, case F8: `protection_policy => 'ALLOW'` is accepted. The middleware then treats it as deny, and `GuardFactory` falls back to the trait default `deny`. No error tells the user.
- Before:

```php
public function setProtectionPolicy(string $protectionPolicy): void
{
    $this->protectionPolicy = $protectionPolicy;
}
```

- After:

```php
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
```

- Why: a typo in a security setting must fail early. Apply the same check in `ProtectionPolicyTrait`.

### F7. `ForbiddenHandler` treats every exception with code 403 as an authorization failure (Low, decision needed)

- Location: `src/Middleware/ForbiddenHandler.php:63-72`, `:99-101`.
- Evidence: `repro.php`, case F7. A `RuntimeException('upstream API said 403', 403)` from the handler becomes `ForbiddenException: You don't have enough permissions…`. Case F6: without a renderer the rethrown `ForbiddenException` has no previous exception, so the original stack trace is lost.
- Before:

```php
} catch (Throwable $e) {
    if (in_array($e->getCode(), $this->authorizationStatusCodes)) {
        return $this->handleForbiddenError($e, $request);
    }
    throw $e;
}
// ...
if (! $this->renderer) {
    throw new ForbiddenException($message);
}
```

- After:

```php
} catch (ForbiddenException $e) {
    return $this->handleForbiddenError($e, $request);
}
// ...
if ($this->renderer === null) {
    throw new ForbiddenException($message, 403, $error instanceof Exception ? $error : null);
}
```

- Why: an HTTP client error from another service is not an RBAC decision. The loose match hides real errors. This is a break for code that throws its own exception with code 403 and expects the 403 page. Option: keep the code match but make it opt-in through a new option. Remove `$authorizationStatusCodes` if the match is removed.
- Note: `ForbiddenException::__construct()` accepts `?Exception $previous`, not `?Throwable` (read in `vendor/dotkernel/dot-authorization/src/Exception/ForbiddenException.php`).

### F8. `RbacGuardOptionsFactory` warns when `dot_authorization` is missing (Low)

- Location: `src/Factory/RbacGuardOptionsFactory.php:19`.
- Evidence: `repro.php`, case F9: `Undefined array key "dot_authorization"`. The `ConfigProvider` normally adds the key, so this happens only when the provider is not registered.
- Before:

```php
return new $requestedName($container->get('config')['dot_authorization']);
```

- After:

```php
$config = $container->get('config')['dot_authorization'] ?? [];

return new RbacGuardOptions($config);
```

- Why: no warning, and a fixed class name. The return type becomes `RbacGuardOptions` instead of `mixed`. The same applies to `GuardFactory`, `PermissionGuardFactory` and `GuardsProviderFactory`, which return `mixed` today. They can return `GuardInterface` and `GuardsProviderInterface`.

### F9. Wrong class names and old API in the docs (Medium, docs)

- Location: `README.md`, `docs/book/v1/configuration.md`, `docs/book/v1/updating-pipeline.md`.
- Evidence:
    - `README.md` uses `\Dot\Rbac\Guard\Middleware\RbacGuardMiddleware::class`. That class is in `dotkernel/dot-rbac-guard`, which conflicts with this package. The correct class is `\Dot\Rbac\Route\Guard\Middleware\RbacGuardMiddleware`.
    - `configuration.md` uses `\Dot\Rbac\Guard\GuardInterface::POLICY_ALLOW`. The correct class is `\Dot\Rbac\Route\Guard\Guard\GuardInterface`.
    - Both pipeline examples use `ApplicationFactory::ROUTING_MIDDLEWARE` and `ApplicationFactory::DISPATCH_MIDDLEWARE`. These constants are from Expressive 2. `mezzio/mezzio-router` 3.20.0 has `Mezzio\Router\Middleware\RouteMiddleware` and `DispatchMiddleware` (read in `vendor/`).
    - No page tells the user to add `ForbiddenHandler` to the pipeline.
- After (pipeline example for v2 docs):

```php
use Dot\Rbac\Route\Guard\Middleware\ForbiddenHandler;
use Dot\Rbac\Route\Guard\Middleware\RbacGuardMiddleware;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;

$app->pipe(RouteMiddleware::class);
// ...
$app->pipe(ForbiddenHandler::class);
$app->pipe(RbacGuardMiddleware::class);
// ...
$app->pipe(DispatchMiddleware::class);
```

- Why: users copy the examples. The order `ForbiddenHandler` before `RbacGuardMiddleware` lets the handler catch the `ForbiddenException`. This order is the same in the Dotkernel Admin `config/pipeline.php` (with `dot-rbac-guard`).
- Run every PHP snippet in v2 docs in a scratch directory before the release (T17).

### F10. Nullable getters for values that are never null (Low, part of V3)

- Location: `src/Guard/RouteGuard.php:21,54`, `src/Guard/RoutePermissionGuard.php:25,114`, `src/Provider/AbstractGuardsProvider.php:12,25`, `src/Options/RbacGuardOptions.php:54`.
- Evidence: the constructors throw when the service is missing, so the value is never null after construction. PHPStan reports 7 `method.nonObject` errors.
- Before:

```php
protected ?RoleServiceInterface $roleService = null;

public function getRoleService(): ?RoleServiceInterface
```

- After:

```php
protected RoleServiceInterface $roleService;

public function getRoleService(): RoleServiceInterface
```

- Same for `getAuthorizationService()`, `getGuardFactory()` and `getMessagesOptions()`.
- Why: the type tells the truth and removes the errors without `assert()`. This is a break for a subclass that overrides a getter with a nullable return type.

### F11. Test quality and coverage gaps

- Evidence: 42 tests. No test file exists for `RbacGuardMiddleware`, `RbacGuardMiddlewareFactory`, `ForbiddenHandlerFactory` or `AbstractAuthorizationEventListener`. `AttachAuthorizationEventListenersTraitTest` uses options without `event_listeners`, so it asserts only one `get()` call and never attaches a listener. `ForbiddenHandlerTest` never uses a renderer, so the 403 page path (`ForbiddenHandler.php:103-121`) has no test. I could not measure coverage, because no driver is installed.
- Plan:
    - Add `test/Middleware/RbacGuardMiddlewareTest.php`: no route result, policy table (F2), guest gets `UnauthorizedException` 401, identity gets `ForbiddenException` 403, listener short-circuit (F1), non-guard object gives `RuntimeException`.
    - Add `test/Factory/RbacGuardMiddlewareFactoryTest.php` and `test/Factory/ForbiddenHandlerFactoryTest.php` (F3).
    - Add tests for the `ForbiddenHandler` renderer path, debug data and listener response.
    - Make `AttachAuthorizationEventListenersTraitTest` attach a string listener, an array listener with priority, a container listener, and an invalid listener (`RuntimeException`).
    - Add tests for F4, F5, F6, F7 and F8.
    - Prefer a fixture container and real `RouteResult` objects over mocks. Replace `createMock(ServerRequest::class)` with `(new ServerRequest())->withAttribute(RouteResult::class, RouteResult::fromRoute(...))`.
    - Run coverage in CI with PCOV (`codecov.yml` already uses it). Mutation testing (Infection) is optional. It is not installed.
- No annotation-reader trap applies here. The package reads no annotations.

## Documentation

`docs/book/index.md` is a symlink to `../../README.md`. Create `docs/book/v2` as a copy of `docs/book/v1`. Keep `docs/book/v1` unchanged, except a note at the top of `overview.md` that points to v2 (optional).

| Page | Required change |
| --- | --- |
| `docs/book/v2/overview.md` | Badge version `0.2.0` to `2.0.0`. Add a short list of the parts: guards, guards provider, middleware, forbidden handler, events |
| `docs/book/v2/installation.md` | Add requirements (PHP 8.3 to 8.6, `dot-rbac` configured first). Add the `ConfigProvider` registration line |
| `docs/book/v2/configuration.md` | Fix the `GuardInterface` namespace (F9). Explain guard evaluation (F2), per-guard `protection_policy`, rule formats (list, `['*']`, `permissions` plus `condition`), empty rules (F4), valid policies (F6). If V6 option A: only `factories` and `aliases` in `guard_manager` and `guards_provider_manager` |
| `docs/book/v2/updating-pipeline.md` | Replace the Expressive 2 example with the Mezzio 3 `pipeline.php` example, and add `ForbiddenHandler` (F9) |
| `docs/book/v2/events.md` (new) | The three events, listener config (`event_listeners`), `AbstractAuthorizationEventListener`, how to return a response from a listener (F1) |
| `docs/book/v2/upgrading.md` (new) | "Upgrading from 0.x to 2.0" table, see below |
| `mkdocs.yml` | `current_version: v2`, `versions: [v2, v1]`, a `v2` nav block with the six pages, keep the `v1` block |
| `README.md` | Fix the namespace and pipeline (F9). Badge version `2.0.0`. Add a version table: `2.x` supported, PHP 8.3 to 8.6. `0.x` security fixes only or not supported (your choice) |
| `SECURITY.md` | Rows for `2.x` and `0.x`. Replace `1.x`. Badge version `2.0.0` |
| `OSSMETADATA` | No change. `osslifecycle=active` is correct |
| `authorization-guards.global.php.dist` | Add a commented `protection_policy` example in a guard's `options` (F2) |

Draft table for `upgrading.md`:

| Change | Symptom | Fix |
| --- | --- | --- |
| PHP 8.2 is not supported | Composer refuses to install | Use PHP 8.3 or newer |
| Under the deny policy, every guard must grant (F2) | Routes that passed before now return 403 | List the route in every guard, or set `protection_policy => allow` in the guard's `options` |
| A route listed without permissions denies all (F4) | 403 on that route | Use `['*']` to allow everyone |
| An invalid `protection_policy` throws (F6) | `InvalidArgumentException` at start | Use `allow` or `deny` |
| `ForbiddenHandler` handles only `ForbiddenException` (F7) | Your own 403-code exception reaches the error handler | Throw `Dot\Authorization\Exception\ForbiddenException` |
| `dispatchEvent()` returns `AuthorizationEvent\|ResponseInterface` (F1) | Type error in code that overrides or calls it | Update the override signature |
| Getters are not nullable (F10) | Fatal error in subclasses with `?Type` returns | Remove the `?` |
| Plugin managers no longer extend `AbstractPluginManager` (V6, only if option A) | `invokables`, `delegators` and similar keys are ignored | Register a factory for each guard |

## CHANGELOG

Done. The entries are in `CHANGELOG.md`: `2.0.0 - TBD` with Added, Changed, Deprecated, Removed, Fixed and Security, then `0.2.0 - 2025-11-03` and `0.1.0 - 2025-02-27`.
The `0.x` entries come from the tag dates and commit `bcc3f82`.
The "Added" line about the direct requirements is true only after the maintainer applies the `composer.json` edit.

## Rollout order

1. Settle the decisions: the branch (V7), version number, F2, F7 and V6.
2. Take the test changes from `origin/upgrade` (V7).
3. Update `phpunit.xml` (V4). Replace mocks without expectations with stubs. Run `composer test`.
4. Fix the install step for PHP 8.6 in two workflows (V2).
5. Add types for PHPStan level 8 that do not change behaviour (V3, docblocks and `AuthorizationEvent`). Run `composer check`.
6. Fix F3 and F8 with tests.
7. Fix F1 with tests.
8. Fix F10 (part of V3). PHPStan must report 0 errors after this step.
9. Fix F5 and F6 with tests.
10. Fix F4, F2 and F7 (behaviour changes) with tests.
11. Add the missing tests (F11).
12. The maintainer applies the `composer.json` edits (V5) and runs `composer update`. I re-run all checks.
13. If V6 option A: replace the plugin managers, the test fixtures, then the maintainer removes `laminas/laminas-servicemanager`.
14. Create `docs/book/v2`, the new pages, and update `mkdocs.yml`, `README.md`, `SECURITY.md` and `authorization-guards.global.php.dist`.
15. Write `CHANGELOG.md`.
16. Run the verification checklist. Push and let CI run on PHP 8.3 to 8.6.

## Verification checklist

- [x] `composer check` passes on PHP 8.5.13: phpcs clean, 120 tests and 227 assertions, PHPStan level 8 with 0 errors.
- [x] `vendor/bin/phpunit` passes with the `failOn*` flags and reports 0 PHPUnit notices.
- [x] The test suite passes with the sibling branches `dot-event` 5.0.0, `dot-rbac` 5.0.0, `dot-authorization` 4.0.0 and `dot-authentication` 3.0.0 loaded by a scratch bootstrap (`bootstrap-next.php`).
- [x] `php repro.php` (scratch) shows the fixed result for F1 to F9.
- [x] `php integration.php <repo> admin|user|guest` (scratch, real `ServiceManager` and `dot-rbac` 4.2.1) gives `handled`, 403 and 401.
- [x] `php integration-next.php <repo> admin|user|guest allow|deny` (scratch, sibling branches, a factory-only PSR-11 container, `Laminas\ServiceManager` blocked) gives `handled`, 403 and 401 for both policies.
- [x] `grep -rn "ServiceManager" src test` returns nothing.
- [x] `grep -rn "Dot\\Rbac\\Guard\\" README.md docs/book/v2` returns nothing.
- [x] Each PHP snippet in `docs/book/v2` and `README.md` parses. Config fragments were checked inside an array.
- [x] The configuration example of `configuration.md` and `authorization-guards.global.php.dist` create the guards and give the documented results (`doc-config.php`, scratch).
- [x] `mkdocs.yml` nav lists only files that exist.
- [x] Every new or changed `.md` file passes `npx --yes markdownlint-cli2 --config ~/.claude/.markdownlint.jsonc`.
- [x] The workflow files are valid YAML.
- [x] Maintainer: applied the `composer.json` edit and ran `composer update`. All checks above were re-run after the update and pass. Composer resolved `dot-authentication` 2.7.0, `dot-authorization` 3.8.1, `dot-event` 4.4.0 and `dot-rbac` 4.2.1, because the new majors are not on Packagist yet.
- [ ] Unverified here, check in CI: tests on PHP 8.3, 8.4 and 8.6.
- [ ] Unverified here, check in CI: coverage with PCOV, and the Codecov upload.
- [ ] Unverified here, check in CI: `composer update --prefer-lowest` on PHP 8.3, for the lower bounds (`laminas-eventmanager ^3.16`, `psr/container ^1.1`).
- [ ] Unverified here, check in CI: the Laminas backward-compatibility job (V8) and its baseline tag.
- [ ] Unverified here: `shivammathur/setup-php@v2` provides PHP 8.6, and the expression in the install step gives the flag only for 8.6.
- [ ] Unverified here: the `mkdocs` build. `mkdocs` is not installed.
- [ ] Before the tag: tighten the `dotkernel/*` ranges to the new majors after they are released (see "Edits still needed").
