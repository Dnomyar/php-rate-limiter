<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Test\Domain\Model;

use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Bucket;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\Duration;
use Damienraymond\PhpFileSystemRateLimiter\Test\Domain\Model\DataTimeProviderMock;
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
//
//    public function testThatWhenTheDelayIsElapsedTheBucketIsReset()
//    {
//        $dataTimeProviderMock = new DataTimeProviderMock();
//        $dataTimeProviderMock->setDateTime(new \DateTime());
//        $bucket = Bucket::createBucket(
//            BucketSize::createBucketSize(5),
//            new BucketTime(Duration::seconds(10), $dataTimeProviderMock)
//        );
//        for ($i = 0; $i < 5; $i++) {
//            $this->assertTrue($bucket->callAllowed());
//            $bucket = $bucket->tryDecreaseOrTimeReset();
//        }
//        $this->assertFalse($bucket->callAllowed());
//
//        $dataTimeProviderMock->setDateTime((new \DateTime)->sub(\DateInterval::createFromDateString('11 seconds')));
//
//        for ($i = 0; $i < 5; $i++) {
//            $this->assertTrue($bucket->callAllowed());
//            $bucket = $bucket->tryDecreaseOrTimeReset();
//        }
////        $this->assertFalse($bucket->callAllowed());
//    }

}
