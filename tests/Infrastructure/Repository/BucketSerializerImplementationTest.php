<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Test\Infrastructure\Repository;


use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Bucket;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\Duration;
use Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository\BucketSerializerImplementation;
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
