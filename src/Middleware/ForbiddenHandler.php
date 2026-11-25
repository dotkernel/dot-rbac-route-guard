<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Middleware;

use Dot\Authorization\AuthorizationInterface;
use Dot\Authorization\Exception\ForbiddenException;
use Dot\Rbac\Route\Guard\Event\AuthorizationEvent;
use Dot\Rbac\Route\Guard\Event\AuthorizationEventListenerInterface;
use Dot\Rbac\Route\Guard\Event\AuthorizationEventListenerTrait;
use Dot\Rbac\Route\Guard\Event\DispatchAuthorizationEventTrait;
use Dot\Rbac\Route\Guard\Options\MessagesOptions;
use Dot\Rbac\Route\Guard\Options\RbacGuardOptions;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ForbiddenHandler implements MiddlewareInterface, AuthorizationEventListenerInterface
{
    use AuthorizationEventListenerTrait;
    use DispatchAuthorizationEventTrait;

    public const TEMPLATE_DEFAULT = 'error::403';

    protected bool $debug = false;

    protected ?TemplateRendererInterface $renderer;

    public function __construct(
        protected AuthorizationInterface $authorizationService,
        protected RbacGuardOptions $options,
        ?TemplateRendererInterface $templateRenderer = null,
        protected string $template = self::TEMPLATE_DEFAULT
    ) {
        $this->renderer = $templateRenderer;
    }

    /**
     * @throws ForbiddenException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ForbiddenException $e) {
            return $this->handleForbiddenError($e, $request);
        }
    }

    /**
     * @throws ForbiddenException
     */
    protected function handleForbiddenError(
        ForbiddenException $error,
        ServerRequestInterface $request
    ): ResponseInterface {
        $event = $this->dispatchEvent(AuthorizationEvent::EVENT_FORBIDDEN, [
            'request'              => $request,
            'authorizationService' => $this->authorizationService,
            'error'                => $error,
        ]);
        if ($event instanceof ResponseInterface) {
            return $event;
        }

        $eventRequest = $event->getParam('request');
        $request      = $eventRequest instanceof ServerRequestInterface ? $eventRequest : $request;

        $message = $error->getMessage();
        if ($message === '') {
            $message = $this->options->getMessagesOptions()->getMessage(MessagesOptions::FORBIDDEN);
        }

        // Without a template renderer, rethrow, so that the outermost error handler processes the error.
        if ($this->renderer === null) {
            throw new ForbiddenException($message, 403, $error);
        }

        $response     = new HtmlResponse('', 403);
        $templateData = [
            'request' => $request,
            'uri'     => $request->getUri(),
            'message' => $message,
            'status'  => $response->getStatusCode(),
            'reason'  => $response->getReasonPhrase(),
        ];
        if ($this->isDebug()) {
            $templateData['error'] = $error;
        }

        return new HtmlResponse($this->renderer->render($this->template, $templateData), 403);
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }
}
