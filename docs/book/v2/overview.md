# Overview

`dot-rbac-route-guard` is Dotkernel's RBAC route guard component.

The package gives authorization guards that control access to the routes of a Mezzio application.
The authorization service of `dotkernel/dot-rbac` checks permissions at a narrow level, for example in a handler.
The guards work as gateways to larger parts of an application, before the handler runs.
Use both methods together for better security.

## Parts of the package

| Part | Purpose |
| --- | --- |
| `RouteGuard` | Grants access to a route by identity role |
| `RoutePermissionGuard` | Grants access to a route by permission |
| `ArrayGuardsProvider` | Creates the guards from the configuration |
| `RbacGuardMiddleware` | Asks every guard about the matched route, then allows or stops the request |
| `ForbiddenHandler` | Catches a `ForbiddenException` and renders the 403 page |
| Authorization events | Let your listeners change the request or the decision, or return a response |

The guards and the guards providers are created by small plugin managers.
The plugin managers use only `psr/container`, so the package works with any PSR-11 container that accepts a factory for each service key.

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
