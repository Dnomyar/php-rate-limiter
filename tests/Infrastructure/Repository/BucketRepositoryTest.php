<?php

namespace Dnomyar\PhpFileSystemRateLimiter\Test\Infrastructure\Repository;


use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Bucket;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\Duration;
use Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository\BucketSerializerImplementation;
use Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository\FileSystemBucketRepository;
use Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository\FileSystemFileAdapterImplementation;
use Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository\InMemoryBucketRepository;
use Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository\LockFileImplementation;
use Dnomyar\PhpFileSystemRateLimiter\Test\Domain\Model\DateTimeProviderMock;
use PHPUnit\Framework\TestCase;

class BucketRepositoryTest extends TestCase
{
    private string $id = "test";

    /**
     * @dataProvider provideRepository
     */
    public function testThatItReturnsTheInitialBucket($bucketRepository)
    {
        $dataTimeProviderMock = new DateTimeProviderMock();
        $now = new \DateTimeImmutable();
        $dataTimeProviderMock->setDateTime($now);
        $bucket = $bucketRepository->upsert(
            $this->id,
            function (Bucket $bucket) {
                return $bucket;
            },
            Bucket::createBucket(
                BucketSize::createBucketSize(6),
                new BucketTime(Duration::seconds(60), $dataTimeProviderMock)
            )
        );
        $this->assertEquals($bucket->getBucketSize()->getInitialSize(), 6);
        $this->assertEquals($bucket->getBucketSize()->getCurrentSize(), 6);
        $this->assertEquals($bucket->getBucketTime()->getTimeLastReset()->getTimestamp(), $now->getTimestamp());
        $this->assertEquals($bucket->getBucketTime()->getDuration()->getSeconds(), 60);
    }

    /**
     * @dataProvider provideRepository
     */
    public function testThatItBucketIsDecreasedAndReturnsTheDecreasedBucket($bucketRepository)
    {
        $dataTimeProviderMock = new DateTimeProviderMock();
        $now = new \DateTimeImmutable();
        $dataTimeProviderMock->setDateTime($now);
        $bucket = $bucketRepository->upsert(
            $this->id,
            function (Bucket $bucket) {
                return $bucket->decrease();
            },
            Bucket::createBucket(
                BucketSize::createBucketSize(6),
                new BucketTime(Duration::seconds(60), $dataTimeProviderMock)
            )
        );
        $this->assertEquals($bucket->getBucketSize()->getInitialSize(), 6);
        $this->assertEquals($bucket->getBucketSize()->getCurrentSize(), 5);
        $this->assertEquals($bucket->getBucketTime()->getTimeLastReset()->getTimestamp(), $now->getTimestamp());
        $this->assertEquals($bucket->getBucketTime()->getDuration()->getSeconds(), 60);
    }

    /**
     * @dataProvider provideRepository
     */
    public function testThatBucketGetResetAndReturnsTheDecreasedBucket($bucketRepository)
    {
        $dataTimeProviderMock = new DateTimeProviderMock();
        $dataTimeProviderMock->setDateTime(new \DateTimeImmutable());
        $bucketTime = new BucketTime(Duration::seconds(60), $dataTimeProviderMock);
        $nowPlus61s = (new \DateTimeImmutable())->add(\DateInterval::createFromDateString('61 seconds'));
        $dataTimeProviderMock->setDateTime($nowPlus61s);
        $bucket = $bucketRepository->upsert(
            $this->id,
            function (Bucket $bucket) {
                return $bucket->resetIfIntervalIsElapsed();
            },
            Bucket::createBucket(
                BucketSize::createBucketSize(6)->decrease()->decrease(),
                $bucketTime
            )
        );
        $this->assertEquals($bucket->getBucketSize()->getInitialSize(), 6);
        $this->assertEquals($bucket->getBucketSize()->getCurrentSize(), 6);
        $this->assertEquals($bucket->getBucketTime()->getTimeLastReset()->getTimestamp(), $nowPlus61s->getTimestamp());
        $this->assertEquals($bucket->getBucketTime()->getDuration()->getSeconds(), 60);
    }
    /**
     * @dataProvider provideRepository
     */
    public function testThatItSavesAndRetrievesTheBucket($bucketRepository){
        $initialBUcket = Bucket::createBucket(
            BucketSize::createBucketSize(6),
            new BucketTime(Duration::seconds(60))
        );
        $bucketRepository->upsert(
            $this->id,
            function (Bucket $bucket) {
                return $bucket;
            },
            $initialBUcket
        );
        $returnBucket = $bucketRepository->upsert(
            $this->id,
            function (Bucket $bucket) {
                return $bucket;
            },
            Bucket::createBucket(
                BucketSize::createBucketSize(89),
                new BucketTime(Duration::seconds(10))
            )
        );
        $this->assertEquals($returnBucket->getBucketSize()->getInitialSize(), $initialBUcket->getBucketSize()->getInitialSize());
        $this->assertEquals($returnBucket->getBucketSize()->getCurrentSize(), $initialBUcket->getBucketSize()->getCurrentSize());
        $this->assertEquals($returnBucket->getBucketTime()->getTimeLastReset(), $initialBUcket->getBucketTime()->getTimeLastReset());
        $this->assertEquals($returnBucket->getBucketTime()->getDuration(), $initialBUcket->getBucketTime()->getDuration());
    }

    public function provideRepository()
    {
        return array(
            array(new InMemoryBucketRepository()),
            array(new FileSystemBucketRepository(
                new FileSystemFileAdapterImplementation(),
                new BucketSerializerImplementation(),
                new LockFileImplementation()
            ))
        );
    }

    protected function tearDown(): void
    {
        try {
            unlink('./' . $this->id);
            unlink('./' . $this->id . '.lock');
        } catch (\Exception $e) {
        }
    }


}
