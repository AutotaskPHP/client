<?php

namespace Autotask\Client;

use UnexpectedValueException;

final class ClientManager
{
    /** @var array<array-key,Client> $clients */
    private static array $clients = [];

    public static function add(string $name, Client $client): void
    {
        self::$clients[$name] = $client;
    }

    /**
     * @throws UnexpectedValueException
     */
    public static function get(string $name): Client
    {
        return self::$clients[$name] ?? throw new UnexpectedValueException(
            "No client with the name [$name] has been set."
        );
    }

    public static function has(string $name): bool
    {
        return array_key_exists($name, self::$clients);
    }

    public static function remove(string $name): void
    {
        unset(self::$clients[$name]);
    }
}
