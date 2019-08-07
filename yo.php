<?php

require 'vendor/autoload.php';

use Aws\DynamoDb\Marshaler;
use Aws\DynamoDb\DynamoDbClient;
use Guillermoandrae\Fisher\Db\DynamoDb\DynamoDbAdapter;

// environment configuration
set_time_limit(3000);

// constants!
define('OLD_TABLE_NAME', 'posts');
define('NEW_TABLE_NAME', 'social-media-posts');

// our Lambda function
lambda(function () {
    // adapter setup
    $adapter = new DynamoDbAdapter(
        new DynamoDbClient([
            'region' => 'us-east-1',
            'version'  => 'latest',
        ]),
        new Marshaler()
    );

    // if the new table exists, get rid of it and start from scratch
    if ($adapter->useTable(NEW_TABLE_NAME)->tableExists()) {
        $adapter->useTable(NEW_TABLE_NAME)->deleteTable();
    }
    $adapter->useTable(NEW_TABLE_NAME)->createTable([]);

    // fetch the old posts
    $oldPosts = $adapter->useTable(OLD_TABLE_NAME)->findAll();
    printf('Fetched %d results!', count($oldPosts));
    echo PHP_EOL;
    exit;

    // loop through the old results
    printf('Looping through the items...');
    echo PHP_EOL;
    foreach ($oldPosts as $item) {
        
        // create the new Item
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

        // insert the new item into the new table
        $adapter->useTable(NEW_TABLE_NAME)->insert($newItem);
        printf('Successfully added item: %s', \json_encode($newItem));
        echo PHP_EOL;
        sleep(.5);
    }
});
