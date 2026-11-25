<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Factory;

use Dot\Authorization\AuthorizationInterface;
use Dot\Rbac\Route\Guard\Exception\RuntimeException;
use Dot\Rbac\Route\Guard\Middleware\ForbiddenHandler;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function is_a;
use function is_array;
use function is_string;
use function sprintf;

class ForbiddenHandlerFactory
{
    use AttachAuthorizationEventListenersTrait;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName = ForbiddenHandler::class
    ): ForbiddenHandler {
        if (! is_a($requestedName, ForbiddenHandler::class, true)) {
            throw new RuntimeException(sprintf('%s can only create %s', self::class, ForbiddenHandler::class));
        }

        $config = $container->has('config') ? $container->get('config') : [];
        $config = is_array($config) ? $config : [];
        $debug  = ($config['debug'] ?? false) === true;

        $template = $config['mezzio']['error_handler']['template_403'] ?? null;
        $template = is_string($template) ? $template : ForbiddenHandler::TEMPLATE_DEFAULT;

        $authorizationService = $container->get(AuthorizationInterface::class);
        if (! $authorizationService instanceof AuthorizationInterface) {
            throw new RuntimeException(sprintf('Service %s is not configured', AuthorizationInterface::class));
        }

        $renderer = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        $handler = new $requestedName(
            $authorizationService,
            $this->getRbacGuardOptions($container),
            $renderer instanceof TemplateRendererInterface ? $renderer : null,
            $template
        );
        $handler->setDebug($debug);
        $handler->attach($handler->getEventManager(), 1000);

        $this->attachListeners($container, $handler->getEventManager());

        return $handler;
    }
}
