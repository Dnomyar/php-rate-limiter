<?php

namespace Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository;

use Dnomyar\PhpFileSystemRateLimiter\Domain\BucketRepository;
use Dnomyar\PhpFileSystemRateLimiter\Domain\BucketRepositoryException;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Bucket;
use PhpOption\Option;


class FileSystemBucketRepository implements BucketRepository
{
    private FileSystemFileAdapter $fileSystemFileAdapter;
    private BucketSerializer $bucketSerializer;
    private LockFile $lockFile;

    /**
     * @param FileSystemFileAdapter $fileSystemFileAdapter
     * @param BucketSerializer $bucketSerializer
     * @param LockFile $lockFile
     */
    public function __construct(FileSystemFileAdapter $fileSystemFileAdapter, BucketSerializer $bucketSerializer, LockFile $lockFile)
    {
        $this->fileSystemFileAdapter = $fileSystemFileAdapter;
        $this->bucketSerializer = $bucketSerializer;
        $this->lockFile = $lockFile;
    }

    /**
     * @throws BucketRepositoryException when another concurrent request is updating the bucket.
     * The advice is to try again later.
     * If it happens too often, another implementation that has a better support for updating atomically the bucket
     * should be used
     */
    function upsert(string $id, callable $update, Bucket $initialBucket): Bucket
    {
        $filename = './' . $id;

        if (! $this->lockFile->lock($filename)) {
            try {
                $bucket =
                    Option::fromValue($this->fileSystemFileAdapter->get($filename))
                        ->flatMap(function (string $serializedBucket) {
                            return Option::fromValue($this->bucketSerializer->deserialize($serializedBucket));
                        })
                        ->getOrElse($initialBucket);
                $newBucket = $update($bucket);
                $this->fileSystemFileAdapter->save($filename, $this->bucketSerializer->serialize($newBucket));
                return $newBucket;
            } catch (\Exception) {
                return $initialBucket;
            } finally {
                $this->lockFile->unlock($filename);
            }
        } else {
            throw new BucketRepositoryException("Unable to update at the moment, try again");
        }
    }
}