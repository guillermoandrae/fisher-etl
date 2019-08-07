<?php

namespace Guillermoandrae\Fisher;

use Guillermoandrae\Fisher\Db\AdapterInterface;
use Guillermoandrae\Common\CollectionInterface;

final class Sync
{
    /**
     * @var AdapterInterface The adapter.
     */
    private $adapter;

    /**
     * Registers the adapter with this object.
     *
     * @param AdapterInterface $adapter The adapter.
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function sync(): void
    {
        $this->initialize();
        
        $rawData = $this->extract();
        printf('Fetched %d results!', count($rawData));
        echo PHP_EOL;

        printf('Looping through the items...');
        foreach ($rawData as $item) {
            $newItem = $this->transform($item);
            $this->load($newItem);
            printf('Successfully added item: %s', \json_encode($newItem));
            echo PHP_EOL;
            sleep(.5);
        }
    }

    public function initialize(): void
    {
        if ($this->adapter->useTable(NEW_TABLE_NAME)->tableExists()) {
            $this->adapter->useTable(NEW_TABLE_NAME)->deleteTable();
        }
        $this->adapter->useTable(NEW_TABLE_NAME)->createTable([]);
    }
    
    public function extract(): CollectionInterface
    {
        return $this->adapter->useTable(OLD_TABLE_NAME)->findAll();
    }

    public function transform(array $item): array
    {
        $createdAt = $item['createdAt'];
        $originalAuthor = trim($item['originalAuthor'], '|');
        $username = 'guillermoandrae';
        $offset = strlen($username);
        if (strlen($originalAuthor) > $offset) {
            $originalAuthor = trim(substr($originalAuthor, $offset), '|');
        }
        return [
            'source' => $item['source'],
            'createdAt' => (int) (is_numeric($createdAt) ? $createdAt : strtotime($createdAt)),
            'body' => $item['body'],
            'externalId' => $item['externalId'],
            'htmlUrl' => $item['htmlUrl'],
            'thumbnailUrl' => $item['thumbnailUrl'],
            'originalAuthor' => $originalAuthor,
        ];
    }

    public function load(array $item)
    {
        $this->adapter->useTable(NEW_TABLE_NAME)->insert($item);
    }
}