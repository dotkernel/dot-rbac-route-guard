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
use Exception;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function in_array;

class ForbiddenHandler implements MiddlewareInterface, AuthorizationEventListenerInterface
{
    use AuthorizationEventListenerTrait;
    use DispatchAuthorizationEventTrait;

    public const TEMPLATE_DEFAULT = 'error::403';

    protected AuthorizationInterface $authorizationService;

    protected array $authorizationStatusCodes = [403];

    protected RbacGuardOptions $options;

    protected ?TemplateRendererInterface $renderer;

    protected string $template;

    protected bool $debug = false;

    public function __construct(
        AuthorizationInterface $authorizationService,
        RbacGuardOptions $options,
        ?TemplateRendererInterface $templateRenderer = null,
        string $template = self::TEMPLATE_DEFAULT
    ) {
        $this->renderer             = $templateRenderer;
        $this->authorizationService = $authorizationService;
        $this->options              = $options;
        $this->template             = $template;
    }

    /**
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ForbiddenException $e) {
            return $this->handleForbiddenError($e, $request);
        } catch (Throwable $e) {
            if (in_array($e->getCode(), $this->authorizationStatusCodes)) {
                return $this->handleForbiddenError($e, $request);
            }
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    protected function handleForbiddenError(
        mixed $error,
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

        $request = $event->getParam('request');
        $message = $this->options->getMessagesOptions()->getMessage(MessagesOptions::FORBIDDEN);
        if ($error instanceof ForbiddenException || ($this->isDebug() && $error instanceof Throwable)) {
            $message = $error->getMessage();
        }

        // if this package is not installed within a template renderer context, re-throw the ForbiddenException
        // to be caught by the outermost error handler(default expressive handler, whoops in development)
        if (! $this->renderer) {
            throw new ForbiddenException($message);
        }

        $response     = new Response();
        $response     = $response->withStatus(403);
        $templateData = [
            'request' => $request,
            'uri'     => $request->getUri(),
            'message' => $message,
            'status'  => $response->getStatusCode(),
            'reason'  => $response->getReasonPhrase(),
        ];
        if ($this->isDebug()) {
            $templateData += [
                'error' => $error,
            ];
        }

        return new HtmlResponse(
            $this->renderer->render($this->template, $templateData),
            403
        );
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
