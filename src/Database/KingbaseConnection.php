<?php

namespace srcker\Kingbase\Database;

use Illuminate\Database\Connection;
use srcker\Kingbase\Database\Query\Grammars\KingbaseGrammar;

class KingbaseConnection extends Connection
{
    public static function createPdo(array $config): \PDO
    {
        $dsn = sprintf(
            'kdb:host=%s;dbname=%s;port=%s',
            $config['host'],
            $config['database'],
            $config['port'] ?? 54321
        );

        return new \PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_EMULATE_PREPARES   => false,
                \PDO::ATTR_STRINGIFY_FETCHES => false,
            ]
        );
    }

    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new KingbaseGrammar());
    }
}