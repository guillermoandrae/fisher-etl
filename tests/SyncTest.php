<?php

namespace GuillermoandraeTest\Fisher;

use PHPUnit\Framework\TestCase;

final class SyncTest extends TestCase
{
    private static $adapter;

    public static function setUpBeforeClass(): void
    {
        self::$adapter = new DynamoDbClient([
            'region' => 'us-west-2',
            'version'  => 'latest',
            'endpoint' => 'http://localhost:8000',
            'credentials' => [
                'key' => 'not-a-real-key',
                'secret' => 'not-a-real-secret',
            ],
        ]);
        self::$adapter->useTable('posts')->create([

        ]);
    }
}
