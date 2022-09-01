<?php

namespace Dnomyar\PhpFileSystemRateLimiter\Test\Domain\Model;

use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Bucket;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\Duration;
use Dnomyar\PhpFileSystemRateLimiter\Test\Domain\Model\DateTimeProviderMock;
use PHPUnit\Framework\TestCase;

class BucketTest extends TestCase
{

    public function testThatABucketWithAProvidedSize0IsEmpty()
    {
        $bucket = Bucket::createBucket(
            BucketSize::createBucketSize(0),
            new BucketTime(Duration::seconds(10))
        );
        $this->assertFalse($bucket->callAllowed());
    }

    public function testThatABucketWithSize1IsNonEmpty()
    {
        $bucket = Bucket::createBucket(
            BucketSize::createBucketSize(1),
            new BucketTime(Duration::seconds(10))
        );
        $this->assertTrue($bucket->callAllowed());
    }

    public function testThatDecreasingABucketOf1GetEmpty(){
        $bucket = Bucket::createBucket(
            BucketSize::createBucketSize(2),
            new BucketTime(Duration::seconds(10))
        );
        $this->assertFalse($bucket->decrease()->decrease()->callAllowed());
    }

    public function testThatTheBucketIsResetIfTheIntervalIsElapsed(){
        $dateTimeProviderMock = new DateTimeProviderMock();
        $dateTimeProviderMock->setDateTime(new \DateTimeImmutable());
        $bucket = Bucket::createBucket(
            BucketSize::createBucketSize(2),
            new BucketTime(Duration::seconds(10), $dateTimeProviderMock)
        );
        $bucketDecreased = $bucket->decrease();

        $now = new \DateTimeImmutable;
        $timeLastReset = $now->add(\DateInterval::createFromDateString('11 seconds'));
        $dateTimeProviderMock->setDateTime($timeLastReset);

        $maybeBucketDecreased = $bucketDecreased->resetIfIntervalIsElapsed();

        $this->assertEquals($maybeBucketDecreased->getBucketSize()->getCurrentSize(), 2);
        $this->assertEquals(
            $maybeBucketDecreased->getBucketTime()->getTimeLastReset(),
            ($now)->add(\DateInterval::createFromDateString('11 seconds'))
        );
    }


}
