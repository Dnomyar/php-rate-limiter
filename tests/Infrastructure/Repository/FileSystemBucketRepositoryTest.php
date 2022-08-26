<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Test\Infrastructure\Repository;

use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Bucket;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\Duration;
use Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository\BucketSerializerImplementation;
use Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository\BucketSerializer;
use Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository\FileSystemBucketRepository;
use Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository\FileSystemFileAdapterImplementation;
use PHPUnit\Framework\TestCase;

class FileSystemBucketRepositoryTest extends TestCase
{
    private string $id = 'test';

    public function testThatItUsedTheDefaultBucketIfThereIsADeserializationError()
    {
        $bucketSerializerThatFailsToDeserialize = new class() extends BucketSerializerImplementation {
            public function deserialize(string $serializeBucket): ?Bucket
            {
                return null;
            }
        };
        $fileSystemFileAdapter = new FileSystemFileAdapterImplementation();
        $fileSystemFileAdapter->save($this->id,
            (new BucketSerializerImplementation())->serialize(
                Bucket::createBucket(
                    BucketSize::createBucketSize(89),
                    new BucketTime(Duration::seconds(10))
                )
            )
        );
        $fileSystemBucketRepository = new FileSystemBucketRepository(
            $fileSystemFileAdapter,
            $bucketSerializerThatFailsToDeserialize
        );

        $defaultBucket = Bucket::createBucket(
            BucketSize::createBucketSize(8),
            new BucketTime(Duration::seconds(190))
        );
        $returnBucket = $fileSystemBucketRepository->upsert(
            $this->id,
            function (Bucket $bucket) {
                return $bucket;
            },
            $defaultBucket
        );
        $this->assertEquals($returnBucket->getBucketSize()->getInitialSize(), $defaultBucket->getBucketSize()->getInitialSize());
        $this->assertEquals($returnBucket->getBucketSize()->getCurrentSize(), $defaultBucket->getBucketSize()->getCurrentSize());
        $this->assertEquals($returnBucket->getBucketTime()->getTimeLastReset(), $defaultBucket->getBucketTime()->getTimeLastReset());
        $this->assertEquals($returnBucket->getBucketTime()->getDuration(), $defaultBucket->getBucketTime()->getDuration());
    }


    public function testThatItLocksAndUnlockToAvoidOtherRequestsToWriteInTheSameFile()
    {



    }

    protected function tearDown(): void
    {
        try {
            unlink('./' . $this->id);
        } catch (\Exception $e) {
        }
    }

}

