<?php declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use Aws\DynamoDb\Marshaler;
use Aws\DynamoDb\DynamoDbClient;
use Guillermoandrae\Fisher\Db\DynamoDb\DynamoDbAdapter;

// environment configuration
set_time_limit(3000);

// constants!
define('OLD_TABLE_NAME', 'posts');
define('NEW_TABLE_NAME', 'social-media-posts');

// our Lambda function
lambda(function (array $event) {
    $adapter = new DynamoDbAdapter(
        new DynamoDbClient([
            'region' => 'us-east-1',
            'version'  => 'latest',
        ]),
        new Marshaler()
    );
    (new Sync($adapter))->sync();
});
