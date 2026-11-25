# dot-rbac-route-guard

`dot-rbac-route-guard` is Dotkernel's RBAC route guard component.

The package gives authorization guards that control access to the routes of a Mezzio application.
The authorization service of `dotkernel/dot-rbac` checks permissions at a narrow level, for example in a handler.
The guards work as gateways to larger parts of an application, before the handler runs.
Use both methods together for better security.

## Documentation

Documentation is available at: https://docs.dotkernel.org/dot-rbac-route-guard/.

## Versions

| Version | Supported | PHP version |
| --- | --- | --- |
| 2.x | :white_check_mark: | ![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-rbac-route-guard/2.0.0) |
| 0.x | :x: | ![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-rbac-route-guard/0.2.0) |

There is no 1.x release. To upgrade from 0.x, read the [upgrade guide](https://docs.dotkernel.org/dot-rbac-route-guard/v2/upgrading/).

## Badges

![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/dot-rbac-route-guard)
![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-rbac-route-guard/2.0.0)

[![GitHub issues](https://img.shields.io/github/issues/dotkernel/dot-rbac-route-guard)](https://github.com/dotkernel/dot-rbac-route-guard/issues)
[![GitHub forks](https://img.shields.io/github/forks/dotkernel/dot-rbac-route-guard)](https://github.com/dotkernel/dot-rbac-route-guard/network)
[![GitHub stars](https://img.shields.io/github/stars/dotkernel/dot-rbac-route-guard)](https://github.com/dotkernel/dot-rbac-route-guard/stargazers)
[![GitHub license](https://img.shields.io/github/license/dotkernel/dot-rbac-route-guard)](https://github.com/dotkernel/dot-rbac-route-guard/blob/main/LICENSE.md)

[![Build Static](https://github.com/dotkernel/dot-rbac-route-guard/actions/workflows/continuous-integration.yml/badge.svg?branch=main)](https://github.com/dotkernel/dot-rbac-route-guard/actions/workflows/continuous-integration.yml)
[![codecov](https://codecov.io/gh/dotkernel/dot-rbac-route-guard/graph/badge.svg?token=AINDYNvE5P)](https://codecov.io/gh/dotkernel/dot-rbac-route-guard)
[![PHPStan](https://github.com/dotkernel/dot-rbac-route-guard/actions/workflows/static-analysis.yml/badge.svg?branch=main)](https://github.com/dotkernel/dot-rbac-route-guard/actions/workflows/static-analysis.yml)

## Installation

Run this command in the root directory of your project:

```shell
composer require dotkernel/dot-rbac-route-guard
```

The package is built on the authorization service of `dotkernel/dot-rbac`. Composer also installs that package.
Configure `dot-rbac` before you use this package.

The package works with any PSR-11 container that accepts a factory for each service key.
It does not require `laminas/laminas-servicemanager`. If your application uses it, require it in your application.

## Configuration

Add `Dot\Rbac\Route\Guard\ConfigProvider::class` to the `ConfigAggregator` of your application.
Then copy `authorization-guards.global.php.dist` to `config/autoload/authorization-guards.global.php` and change the rules.

A short example:

```php
<?php

declare(strict_types=1);

use Dot\Rbac\Route\Guard\Guard\GuardInterface;

return [
    'dot_authorization' => [
        'protection_policy' => GuardInterface::POLICY_ALLOW,
        'guards_provider'   => [
            'type'    => 'ArrayGuards',
            'options' => [
                'guards' => [
                    [
                        'type'    => 'Route',
                        'options' => [
                            'rules' => [
                                'account' => ['admin', 'user'],
                                'home'    => ['*'],
                            ],
                        ],
                    ],
                    [
                        'type'    => 'RoutePermission',
                        'options' => [
                            'rules' => [
                                'account' => ['my-account'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
```

A request continues only when every guard grants access.
The [configuration page](https://docs.dotkernel.org/dot-rbac-route-guard/v2/configuration/) explains all keys and rule formats.

## Register the middleware in the pipeline

Add `ForbiddenHandler` and `RbacGuardMiddleware` to `config/pipeline.php`, after `RouteMiddleware` and before `DispatchMiddleware`.
The guards need the `RouteResult` that `RouteMiddleware` adds to the request.

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
