<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Fixture;

use Mezzio\Template\TemplateRendererInterface;

/**
 * Renders the template name. It keeps the parameters of the last call.
 */
class TemplateRenderer implements TemplateRendererInterface
{
    /** @var array<array-key, mixed>|object */
    public array|object $lastParams = [];

    /**
     * @param array<array-key, mixed>|object $params
     */
    public function render(string $name, $params = []): string
    {
        $this->lastParams = $params;

        return 'rendered:' . $name;
    }

    public function addPath(string $path, ?string $namespace = null): void
    {
    }

    public function getPaths(): array
    {
        return [];
    }

    public function addDefaultParam(string $templateName, string $param, mixed $value): void
    {
    }
}
