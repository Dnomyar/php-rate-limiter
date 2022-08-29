<?php
declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Domain;

use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Bucket;

class RateLimiter
{
    private string $id;
    private BucketTime $bucketTime;
    private BucketSize $bucketSize;
    private BucketRepository $bucketRepository;

    public function __construct(string $id, BucketTime $bucketTime, BucketSize $bucketSize, BucketRepository $bucketRepository)
    {
        $this->id = $id;
        $this->bucketTime = $bucketTime;
        $this->bucketSize = $bucketSize;
        $this->bucketRepository = $bucketRepository;
    }

    public function allowCall(): bool
    {
        return $this->bucketRepository->upsert(
            $this->id,
            function (Bucket $bucket) {
                return $bucket
                    ->decrease()
                    ->resetIfIntervalIsElapsed();
            },
            Bucket::createBucket($this->bucketSize, $this->bucketTime)
        )->callAllowed();
    }

}