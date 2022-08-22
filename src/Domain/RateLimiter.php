<?php
declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Domain;

use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Bucket;
use phpDocumentor\Reflection\Types\This;
use PhpOption\Option;

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
        $bucket = Option::fromValue($this->bucketRepository->get($this->id))->getOrElse(
            Bucket::createBucket($this->bucketSize, $this->bucketTime)
        );

        $isNonEmpty = $bucket->callAllowed();

        $this->bucketRepository->save($this->id, $bucket->decrease()->resetIfIntervalIsElapsed());

        return $isNonEmpty;
    }

}