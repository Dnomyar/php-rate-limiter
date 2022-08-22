<?php declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Domain\Model;

use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;

class Bucket
{
    private BucketSize $bucketSize;
    private BucketTime $bucketTime;

    private function __construct(BucketSize $bucketSize, BucketTime $bucketTime)
    {
        $this->bucketSize = $bucketSize;
        $this->bucketTime = $bucketTime;
    }

    public static function createBucket(BucketSize $bucketSize, BucketTime $bucketTime)
    {
        return new Bucket($bucketSize, $bucketTime);
    }

    public function decrease(): Bucket{
        return new Bucket($this->bucketSize->decrease(), $this->bucketTime);
    }

    public function callAllowed(): bool
    {
        return $this->bucketSize->getCurrentSize() !== 0;
    }

    public function resetIfIntervalIsElapsed(): Bucket
    {
        if ($this->bucketTime->intervalIsElapsed()) {
            return new Bucket($this->bucketSize->reset(), $this->bucketTime->reset());
        }
        return $this;
    }

}