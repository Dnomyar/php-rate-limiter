<?php

namespace Dnomyar\PhpFileSystemRateLimiter\Test\Infrastructure\Repository;


use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Bucket;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\Duration;
use Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository\BucketSerializerImplementation;
use PHPUnit\Framework\TestCase;

class BucketSerializerImplementationTest extends TestCase
{

    /**
     * @dataProvider provideBuckets
     */
    public function testThatItCanSerializeAndDeserializeABucket(Bucket $bucket)
    {
        $bucketSerializerImplementation = new BucketSerializerImplementation();
        $deserializedBucket = $bucketSerializerImplementation->deserialize(
            $bucketSerializerImplementation->serialize($bucket)
        );

        $this->assertEquals($bucket, $deserializedBucket);
    }

    public function provideBuckets(){
        return array(
            array(Bucket::createBucket(
                BucketSize::createBucketSize(6),
                new BucketTime(Duration::seconds(60))
            )),
            array(Bucket::createBucket(
                BucketSize::createBucketSize(100)->decrease()->decrease(),
                new BucketTime(Duration::seconds(10))
            )),
            array(Bucket::createBucket(
                BucketSize::createBucketSize(100)->decrease()->decrease(),
                new BucketTime(Duration::seconds(10))
            )),
        );
    }

}
