<?php

declare(strict_types=1);

namespace Dot\Rbac\Route\Guard\Options;

use Laminas\Stdlib\AbstractOptions;
use Laminas\Stdlib\ParameterObjectInterface;

use function is_int;
use function is_string;

/**
 * @extends AbstractOptions<ParameterObjectInterface>
 */
class MessagesOptions extends AbstractOptions
{
    public const UNAUTHORIZED = 0;
    public const FORBIDDEN    = 1;

    /** @var array<int, string> */
    protected array $messages = [
        self::UNAUTHORIZED => 'You must sign in first in order to access the requested content',
        self::FORBIDDEN    => 'You don\'t have enough permissions to access the requested content',
    ];

    /**
     * @param iterable<string, mixed>|null $options
     */
    public function __construct(?iterable $options = null)
    {
        $this->__strictMode__ = false;
        parent::__construct($options);
    }

    /**
     * @return array<int, string>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Replaces the default messages by key. Use the MessagesOptions::UNAUTHORIZED and ::FORBIDDEN keys.
     *
     * @param array<array-key, mixed> $messages
     */
    public function setMessages(array $messages): void
    {
        foreach ($messages as $key => $message) {
            if (is_int($key) && is_string($message)) {
                $this->messages[$key] = $message;
            }
        }
    }

    public function getMessage(int $key): string
    {
        return $this->messages[$key] ?? '';
    }
}
