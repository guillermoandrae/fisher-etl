<?php

require 'vendor/autoload.php';

use Aws\Sdk;
use Aws\Result;
use Aws\MockHandler;
use Aws\CommandInterface;
use Aws\DynamoDb\Marshaler;
use PHPUnit\Framework\TestCase;
use Aws\Exception\AwsException;
use Psr\Http\Message\RequestInterface;
use Guillermoandrae\Common\Collection;
use Guillermoandrae\Fisher\Models\PostModel;
use Guillermoandrae\Fisher\Repositories\PostsRepository;

// setup
set_time_limit(5000);
$marshaler = new Marshaler();
$sdk = new Sdk([
    'region' => 'us-east-1',
    'version'  => 'latest',
]);
$dynamoDb = $sdk->createDynamoDb();

// fetch the posts
$results = $dynamoDb->scan(['TableName' => 'posts']);
$old = [];
foreach ($results['Items'] as $item) {
    $old[] = $marshaler->unmarshalItem($item);
}
printf('Fetched %d results!', count($old));
echo PHP_EOL;

// loop through the old results
printf('Looping through the items...');
echo PHP_EOL;
foreach ($old as $item) {
    $createdAt = $item['createdAt'];
    $originalAuthor = trim($item['originalAuthor'], '|');
    $username = 'guillermoandrae';
    $offset = strlen($username);
    if (strlen($originalAuthor) > $offset) {
        $originalAuthor = trim(substr($originalAuthor, $offset), '|');
    }
    $newItem = [
        'source' => $item['source'],
        'createdAt' => (int) (is_numeric($createdAt) ? $createdAt : strtotime($createdAt)),
        'body' => $item['body'],
        'externalId' => $item['externalId'],
        'htmlUrl' => $item['htmlUrl'],
        'thumbnailUrl' => $item['thumbnailUrl'],
        'originalAuthor' => $originalAuthor,
    ];
    $params = [
        'Item' => $marshaler->marshalItem($newItem),
        'TableName' => 'social-media-posts'
    ];
    $result = $dynamoDb->putItem($params);
    printf('Successfully added item: %s', \json_encode($newItem));
    echo PHP_EOL;
    sleep(.5);
}
