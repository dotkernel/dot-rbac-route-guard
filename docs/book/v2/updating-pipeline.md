# Register the middleware in the pipeline

Add two middleware to `config/pipeline.php`:

- `ForbiddenHandler` catches a `ForbiddenException` and renders the 403 page.
- `RbacGuardMiddleware` asks the guards about the matched route.

Add both after `RouteMiddleware` and before `DispatchMiddleware`.
The guards need the `RouteResult` that `RouteMiddleware` adds to the request.
Add `ForbiddenHandler` before `RbacGuardMiddleware`, so that it can catch the exception of the guards.

## pipeline.php

```php
<?php

declare(strict_types=1);

use Dot\Rbac\Route\Guard\Middleware\ForbiddenHandler;
use Dot\Rbac\Route\Guard\Middleware\RbacGuardMiddleware;
use Mezzio\Application;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;

return function (Application $app): void {
    // ...
    $app->pipe(RouteMiddleware::class);

    // ...
    // Add your authentication middleware here, if you use one.

    $app->pipe(ForbiddenHandler::class);
    $app->pipe(RbacGuardMiddleware::class);

    // ...
    $app->pipe(DispatchMiddleware::class);
    // ...
};
```

## The 403 page

`ForbiddenHandler` renders the template `error::403` with these variables: `request`, `uri`, `message`, `status` and `reason`.
When `debug` is `true` in the configuration, the template also gets `error`, the exception.

To use another template, set it in the configuration:

```php
'mezzio' => [
    'error_handler' => [
        'template_403' => 'app::forbidden',
    ],
],
```

When the container has no `Mezzio\Template\TemplateRendererInterface` service, `ForbiddenHandler` throws the `ForbiddenException` again.
Then the error handler of the application processes it.
