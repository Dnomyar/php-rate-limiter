<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository;

use Damienraymond\PhpFileSystemRateLimiter\Domain\BucketRepository;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Bucket;
use PhpOption\Option;


interface FileSystemFileLocker
{
    public function lock(string $filename): bool;

    public function unlock(string $filename): void;
}

class FileSystemBucketRepository implements BucketRepository
{
    private FileSystemFileAdapter $fileSystemFileAdapter;
    private BucketSerializer $bucketSerializer;

    /**
     * @param FileSystemFileAdapter $fileSystemFileAdapter
     * @param BucketSerializer $bucketSerializer
     */
    public function __construct(FileSystemFileAdapter $fileSystemFileAdapter, BucketSerializer $bucketSerializer)
    {
        $this->fileSystemFileAdapter = $fileSystemFileAdapter;
        $this->bucketSerializer = $bucketSerializer;
    }

    function upsert(string $id, callable $update, Bucket $initialBucket): Bucket
    {
        $filename = './' . $id;
        $bucket =
            Option::fromValue($this->fileSystemFileAdapter->get($filename))
                ->flatMap(function (string $serializedBucket) {
                    return Option::fromValue($this->bucketSerializer->deserialize($serializedBucket));
                })
                ->getOrElse($initialBucket);
        $newBucket = $update($bucket);
        $this->fileSystemFileAdapter->save($filename, $this->bucketSerializer->serialize($newBucket));
        return $newBucket;
    }
}