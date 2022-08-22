<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository;

use Damienraymond\PhpFileSystemRateLimiter\Domain\BucketRepository;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Bucket;

class InMemoryBucketRepository implements BucketRepository
{

    private array $buckets;

    public function __construct()
    {
        $this->buckets = [];
    }


    function get(string $id): ?Bucket
    {
        return $this->buckets[$id] ?? null;
    }

    function save(string $id, Bucket $bucket)
    {
        $this->buckets[$id] = $bucket;
    }
}