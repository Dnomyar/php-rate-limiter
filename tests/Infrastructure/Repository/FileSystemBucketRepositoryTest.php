<?php

namespace Dnomyar\PhpFileSystemRateLimiter\Test\Infrastructure\Repository;

use Dnomyar\PhpFileSystemRateLimiter\Domain\BucketRepositoryException;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Bucket;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\Duration;
use Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository\BucketSerializerImplementation;
use Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository\BucketSerializer;
use Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository\FileSystemBucketRepository;
use Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository\FileSystemFileAdapterImplementation;
use Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository\LockFileImplementation;
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
            $bucketSerializerThatFailsToDeserialize,
            new LockFileImplementation()
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


    /**
     * This test is quite implementation dependant
     * I don't know how to test the lock/unlock without that
     */
    public function testThatItLocksAndUnlockToAvoidOtherRequestsToWriteInTheSameFile()
    {
        $operationRegistry = new FileSystemBucketRepositoryOperationsRegistry();
        $fileSystemBucketRepository = new FileSystemBucketRepository(
            $operationRegistry->getFileSystemFileAdapterImplementation(),
            new BucketSerializerImplementation(),
            $operationRegistry->getLockFileImplementation()
        );
        $fileSystemBucketRepository->upsert(
            $this->id,
            function (Bucket $bucket) {
                return $bucket;
            },
            Bucket::createBucket(
                BucketSize::createBucketSize(89),
                new BucketTime(Duration::seconds(10))
            )
        );
        $this->assertEquals([
            FileSystemBucketRepositoryOperationsRegistry::$LOCK,
            FileSystemBucketRepositoryOperationsRegistry::$GET,
            FileSystemBucketRepositoryOperationsRegistry::$SAVE,
            FileSystemBucketRepositoryOperationsRegistry::$UNLOCK,
        ], $operationRegistry->getOperationRegistry());

    }

    public function testThatItUnlocksEvenIfThereIsAnErrorDuringTheProcess()
    {
        $operationRegistry = new FileSystemBucketRepositoryOperationsRegistry();
        $fileSystemBucketRepository = new FileSystemBucketRepository(
            new class extends FileSystemFileAdapterImplementation {
                public function get(string $filename): ?string
                {
                    return parent::get($filename);
                }

                public function save(string $filename, string $toSave): void
                {
                    throw new \Exception('test exception');
                }

            },
            new BucketSerializerImplementation(),
            $operationRegistry->getLockFileImplementation()
        );
        $defaultBucket = Bucket::createBucket(
            BucketSize::createBucketSize(89),
            new BucketTime(Duration::seconds(10))
        );
        $returnBucket = $fileSystemBucketRepository->upsert(
            $this->id,
            function (Bucket $bucket) {
                return $bucket;
            },
            $defaultBucket
        );
        $this->assertContains(
            FileSystemBucketRepositoryOperationsRegistry::$UNLOCK,
            $operationRegistry->getOperationRegistry()
        );
        $this->assertEquals($returnBucket->getBucketSize()->getInitialSize(), $defaultBucket->getBucketSize()->getInitialSize());
        $this->assertEquals($returnBucket->getBucketSize()->getCurrentSize(), $defaultBucket->getBucketSize()->getCurrentSize());
        $this->assertEquals($returnBucket->getBucketTime()->getTimeLastReset(), $defaultBucket->getBucketTime()->getTimeLastReset());
        $this->assertEquals($returnBucket->getBucketTime()->getDuration(), $defaultBucket->getBucketTime()->getDuration());

    }

    public function testItReturnAnErrorIfItIsAlreadyLocked(){
        $operationRegistry = new FileSystemBucketRepositoryOperationsRegistry();
        $fileSystemBucketRepository = new FileSystemBucketRepository(
            $operationRegistry->getFileSystemFileAdapterImplementation(),
            new BucketSerializerImplementation(),
            new class extends LockFileImplementation{
                public function lock(string $file): bool
                {
                    return true;
                }
            }
        );
        $defaultBucket = Bucket::createBucket(
            BucketSize::createBucketSize(89),
            new BucketTime(Duration::seconds(10))
        );
        $this->expectException(BucketRepositoryException::class);
        $fileSystemBucketRepository->upsert(
            $this->id,
            function (Bucket $bucket) {
                return $bucket;
            },
            $defaultBucket
        );
    }


    protected function tearDown(): void
    {
        try {
            unlink('./' . $this->id);
        } catch (\Exception $e) {
        }
    }

}

class FileSystemBucketRepositoryOperationsRegistry
{
    public static string $GET = 'GET';
    public static string $SAVE = 'SAVE';
    public static string $LOCK = 'LOCK';
    public static string $UNLOCK = 'UNLOCK';

    private array $operationRegistry = [];


    public function registerOperation(string $operation): void
    {
        $this->operationRegistry[] = $operation;
    }

    public function getFileSystemFileAdapterImplementation(): FileSystemFileAdapterImplementation
    {
        return new class($this) extends FileSystemFileAdapterImplementation {
            private FileSystemBucketRepositoryOperationsRegistry $operations;

            public function __construct(FileSystemBucketRepositoryOperationsRegistry $operations)
            {
                $this->operations = $operations;
            }

            public function get(string $filename): ?string
            {
                $this->operations->registerOperation(FileSystemBucketRepositoryOperationsRegistry::$GET);
                return parent::get($filename);
            }

            public function save(string $filename, string $toSave): void
            {
                $this->operations->registerOperation(FileSystemBucketRepositoryOperationsRegistry::$SAVE);
                parent::save($filename, $toSave);
            }

        };
    }

    public function getLockFileImplementation()
    {
        return new class($this) extends LockFileImplementation {
            private FileSystemBucketRepositoryOperationsRegistry $operations;

            public function __construct(FileSystemBucketRepositoryOperationsRegistry $operations)
            {
                $this->operations = $operations;
            }

            public function lock(string $file): bool
            {
                $this->operations->registerOperation(FileSystemBucketRepositoryOperationsRegistry::$LOCK);
                return LockFileImplementation::lock($file);
            }

            public function unlock(string $file): void
            {
                $this->operations->registerOperation(FileSystemBucketRepositoryOperationsRegistry::$UNLOCK);
                LockFileImplementation::unlock($file);
            }


        };
    }


    /**
     * @return array
     */
    public function getOperationRegistry(): array
    {
        return $this->operationRegistry;
    }


}


