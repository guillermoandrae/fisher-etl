<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Guillermoandrae\Fisher\Db\DynamoDb\DynamoDbAdapter;
use Guillermoandrae\Fisher\Sync;

// environment configuration
set_time_limit(3000);

// constants!
define('OLD_TABLE_NAME', 'posts');
define('NEW_TABLE_NAME', 'social-media-posts');

try {
    $adapter = new DynamoDbAdapter(
        new DynamoDbClient([
            'region' => 'us-east-1',
            'version' => 'latest',
        ]),
        new Marshaler()
    );
    (new Sync($adapter))->sync();
} catch (\Exception $ex) {
    die($ex->getMessage());
}