<?php

namespace Autotask\Client\Http\Response\Exception;

use LogicException;

final class InvalidRequestException extends LogicException
{
    /**
     * @param array<array-key,string> $errors
     */
    public static function withErrors(array $errors): self
    {
        $message = 'Invalid request. Received the following errors:' . PHP_EOL;
        $message .= join(PHP_EOL, $errors);
        $message .= PHP_EOL . PHP_EOL;

        return new self($message);
    }

    private function __construct(string $message)
    {
        parent::__construct($message);
    }
}