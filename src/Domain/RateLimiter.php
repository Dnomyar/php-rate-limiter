<?php
declare(strict_types=1);

namespace Dnomyar\PhpFileSystemRateLimiter\Domain;

use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Bucket;

class RateLimiter
{
    private BucketTime $bucketTime;
    private BucketSize $bucketSize;
    private BucketRepository $bucketRepository;

    public function __construct(BucketTime $bucketTime, BucketSize $bucketSize, BucketRepository $bucketRepository)
    {
        $this->bucketTime = $bucketTime;
        $this->bucketSize = $bucketSize;
        $this->bucketRepository = $bucketRepository;
    }

    public function allowCall(string $id): bool
    {
        return $this->bucketRepository->upsert(
            $id,
            function (Bucket $bucket) {
                return $bucket
                    ->decrease()
                    ->resetIfIntervalIsElapsed();
            },
            Bucket::createBucket($this->bucketSize, $this->bucketTime)
        )->callAllowed();
    }

}