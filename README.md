# dot-rbac-route-guard

`dot-rbac-route-guard` is Dotkernel's RBAC Route guard component.

Defines authorization guards that authorize users to access certain parts of an application based on various criteria.
If the authorization service can be used to check authorization on a narrow level, the guards are meant to work as gateways to bigger parts of an application.
Usually, you'll want to use both methods in an application for increased security.

## Documentation

Documentation is available at: https://docs.dotkernel.org/dot-rbac-route-guard/.

## Badges

![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/dot-rbac-route-guard)
![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-rbac-route-guard/0.2.0)

[![GitHub issues](https://img.shields.io/github/issues/dotkernel/dot-rbac-route-guard)](https://github.com/dotkernel/dot-rbac-route-guard/issues)
[![GitHub forks](https://img.shields.io/github/forks/dotkernel/dot-rbac-route-guard)](https://github.com/dotkernel/dot-rbac-route-guard/network)
[![GitHub stars](https://img.shields.io/github/stars/dotkernel/dot-rbac-route-guard)](https://github.com/dotkernel/dot-rbac-route-guard/stargazers)
[![GitHub license](https://img.shields.io/github/license/dotkernel/dot-rbac-route-guard)](https://github.com/dotkernel/dot-rbac-route-guard/blob/main/LICENSE.md)

[![Build Static](https://github.com/dotkernel/dot-rbac-route-guard/actions/workflows/continuous-integration.yml/badge.svg?branch=main)](https://github.com/dotkernel/dot-rbac-route-guard/actions/workflows/continuous-integration.yml)
[![codecov](https://codecov.io/gh/dotkernel/dot-rbac-route-guard/graph/badge.svg?token=AINDYNvE5P)](https://codecov.io/gh/dotkernel/dot-rbac-route-guard)
[![PHPStan](https://github.com/dotkernel/dot-rbac-route-guard/actions/workflows/static-analysis.yml/badge.svg?branch=main)](https://github.com/dotkernel/dot-rbac-route-guard/actions/workflows/static-analysis.yml)

## Installation

Run the following Composer command in your project's root directory:

```shell
composer require dotkernel/dot-rbac-route-guard
```

Please note that this library is built around the authorization service defined in `dotkernel/dot-rbac`.
Running the above command will also install that library.
You'll have to first configure `dot-rbac` before using this library.

## Configuration

As with many Dotkernel libraries, we focus on the configuration-based approach of customizing the module for your needs.

After installing, merge `dot-rbac-route-guard`'s `ConfigProvider` with your application's config to make sure required dependencies and default library configuration are registered.
Create a configuration file for this library in your 'config/autoload' folder.

### authorization-guards.global.php

You can copy the below code or use the existing `authorization-guards.global.php.dist` to create your version of `authorization-guards.global.php`.

```php
<?php

declare(strict_types=1);

use Dot\Rbac\Route\Guard\Guard\GuardInterface;

return [
    'dot_authorization' => [
        //define how it will treat non-matching guard rules, allow all by default
        'protection_policy' => \Dot\Rbac\Route\Guard\Guard\GuardInterface::POLICY_ALLOW,
        'event_listeners'   => [
            [
                'type'     => 'class or service name of the listener',
                'priority' => 1,
            ],
        ],

        //define custom guards here
        'guard_manager' => [],

        //register custom guards providers here
        'guards_provider_manager' => [],

        //define which guard provider to use, along with its configuration
        //the guard provider should know how to build a list of GuardInterfaces based on its configuration
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
                            'rules' => [
                                'premium' => ['premium'],
                                'account' => ['my-account'],
                                'logout'  => ['only-logged'],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        //overwrite default messages
        'messages_options' => [
            'messages' => [
                //MessagesOptions::UNAUTHORIZED => 'You must sign in first to access the requested content',
                //MessagesOptions::FORBIDDEN => 'You don\'t have enough permissions to access the requested content',
            ],
        ],
    ],
];
```

## Register RbacGuardMiddleware in the pipeline

The last step to use this package is to register the middleware.
This middleware triggers the authorization event.
You MUST insert this middleware between the routing middleware and the dispatch middleware of the application, because the guards need the `RouteResult` in order to get the matched route and params.

### middleware-pipeline.global.php

```php
//...

'routing' => [
    'middleware' => [
        ApplicationFactory::ROUTING_MIDDLEWARE,

        //...

        \Dot\Rbac\Guard\Middleware\RbacGuardMiddleware::class,

        //...

        ApplicationFactory::DISPATCH_MIDDLEWARE,
    ],
    'priority' => 1,
],

//...
```
