<?php

declare(strict_types=1);

namespace DotTest\Rbac\Route\Guard\Options;

use Dot\Rbac\Route\Guard\Options\MessagesOptions;
use PHPUnit\Framework\TestCase;

class MessagesOptionsTest extends TestCase
{
    public function testDefaultMessages(): void
    {
        $subject = new MessagesOptions();

        $this->assertSame(
            'You must sign in first in order to access the requested content',
            $subject->getMessage(MessagesOptions::UNAUTHORIZED)
        );
        $this->assertSame(
            'You don\'t have enough permissions to access the requested content',
            $subject->getMessage(MessagesOptions::FORBIDDEN)
        );
        $this->assertSame('', $subject->getMessage(1000));
    }

    public function testSetMessagesReplacesByKeyAndIgnoresInvalidEntries(): void
    {
        $subject = new MessagesOptions([
            'messages' => [MessagesOptions::FORBIDDEN => 'No access', 'text' => 'ignored', 5 => ['ignored']],
        ]);

        $this->assertSame('No access', $subject->getMessage(MessagesOptions::FORBIDDEN));
        $this->assertCount(2, $subject->getMessages());
    }
}
