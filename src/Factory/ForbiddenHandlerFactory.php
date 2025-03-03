<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Factory;

use Dot\Authorization\AuthorizationInterface;
use Dot\Rbac\Route\Guard\Middleware\ForbiddenHandler;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function is_bool;

class ForbiddenHandlerFactory
{
    use AttachAuthorizationEventListenersTrait;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName): ForbiddenHandler
    {
        $config = $container->get('config');
        $debug  = is_bool($config['debug']) && $config['debug'];

        $authorizationService = $container->get(AuthorizationInterface::class);
        $moduleOptions        = $container->get(RbacGuardOptions::class);

        $template = $config['mezzio']['error_handler']['template_403'] ?? ForbiddenHandler::TEMPLATE_DEFAULT;

        $renderer = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        /** @var ForbiddenHandler $handler */
        $handler = new $requestedName($authorizationService, $moduleOptions, $renderer, $template);
        $handler->setDebug($debug);
        $handler->attach($handler->getEventManager(), 1000);

        $this->attachListeners($container, $handler->getEventManager());

        return $handler;
    }
}
