<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository;

use Damienraymond\PhpFileSystemRateLimiter\Domain\BucketRepository;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Bucket;
use PhpOption\Option;

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

    function save(string $id, Bucket $bucket): void
    {
        $this->buckets[$id] = $bucket;
    }

    function upsert(string $id, callable $update, Bucket $initialBucket): Bucket
    {
        $bucket = Option::fromValue($this->get($id))->getOrElse($initialBucket);
        $newBucket = $update($bucket);
        $this->save($id, $newBucket);
        return $newBucket;
    }
}