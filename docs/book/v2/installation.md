# Installation

## Requirements

- PHP 8.3, 8.4, 8.5 or 8.6.
- A Mezzio application with `mezzio/mezzio-router`.
- `dotkernel/dot-rbac`, configured with roles and permissions. Configure it before you use this package.
- A PSR-11 container that accepts a factory for each service key, for example Laminas Service Manager.

## Install the package

Run this command in the root directory of your project:

```shell
composer require dotkernel/dot-rbac-route-guard
```

Composer also installs `dotkernel/dot-rbac`.

## Register the ConfigProvider

Add the `ConfigProvider` of the package to `config/config.php`:

```php
$aggregator = new ConfigAggregator([
    // ...
    Dot\Rbac\ConfigProvider::class,
    Dot\Rbac\Route\Guard\ConfigProvider::class,
    // ...
]);
```

The `ConfigProvider` registers the factories of the middleware, the options and the plugin managers.
It also adds the default `dot_authorization` keys.

Then [configure the guards](configuration.md) and [add the middleware to the pipeline](updating-pipeline.md).
