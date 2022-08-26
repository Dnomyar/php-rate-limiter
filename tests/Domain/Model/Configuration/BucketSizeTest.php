<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Test\Domain\Model\Configuration;

use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use PHPUnit\Framework\TestCase;

class BucketSizeTest extends TestCase
{

    public function testThatABucketIsReset(){
        $bucketSize = BucketSize::createBucketSize(6);
        $resetBucket = $bucketSize->decrease()->decrease()->reset();
        $this->assertEquals($bucketSize->getCurrentSize(), $resetBucket->getCurrentSize());
        $this->assertEquals($bucketSize->getInitialSize(), $resetBucket->getInitialSize());
    }

}