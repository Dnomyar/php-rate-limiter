<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository;

use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Bucket;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\DateTimeProvider;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\Duration;
use DateTimeInterface;

class BucketSerializerImplementation implements BucketSerializer
{

    // this is a bit a future proofing if we need to serialize more things in the future
    private int $VERSION = 1;

    private string $SEPARATOR = "|";

    public function serialize(Bucket $bucket): string
    {
        $initialSize = $bucket->getBucketSize()->getInitialSize();
        $currentSize = $bucket->getBucketSize()->getCurrentSize();
        $duration = $bucket->getBucketTime()->getDuration()->getSeconds();
        $dateTimeImmutable = $bucket->getBucketTime()->getTimeLastReset()->format(DateTimeInterface::ATOM);
        $microseconds = $bucket->getBucketTime()->getTimeLastReset()->format('u');

        return implode(
            $this->SEPARATOR,
            array(
                $this->VERSION,
                $initialSize,
                $currentSize,
                $duration,
                $dateTimeImmutable,
                $microseconds
            )
        );
    }

    public function deserialize(string $serializeBucket): ?Bucket
    {
        $exploded = explode($this->SEPARATOR, $serializeBucket);

        if(count($exploded) == 6){
            $initialSize = $exploded[1];
            $currentSize = $exploded[2];
            $duration = $exploded[3];
            $dateTimeImmutable = $exploded[4];
            $microseconds = $exploded[5];

            return Bucket::createBucket(
                new BucketSize($initialSize, $currentSize),
                new BucketTime(
                    Duration::seconds($duration),
                    new DateTimeProvider(),
                    (new \DateTimeImmutable($dateTimeImmutable))->add(\DateInterval::createFromDateString($microseconds . ' microseconds'))
                )
            );
        }
        return null;
    }
}