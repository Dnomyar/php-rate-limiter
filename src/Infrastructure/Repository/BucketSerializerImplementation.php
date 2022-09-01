<?php

namespace Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository;

use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Bucket;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\DateTimeProvider;
use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration\Duration;
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
        try {
            $exploded = explode($this->SEPARATOR, $serializeBucket);
            if (count($exploded) == 6) {
                $initialSize = $exploded[1];
                $currentSize = $exploded[2];
                $duration = $exploded[3];
                $dateTimeImmutable = $exploded[4];
                $microseconds = $exploded[5];

                return Bucket::createBucket(
                    new BucketSize(intval($initialSize), intval($currentSize)),
                    new BucketTime(
                        Duration::seconds(intval($duration)),
                        new DateTimeProvider(),
                        (new \DateTimeImmutable($dateTimeImmutable))->add(\DateInterval::createFromDateString($microseconds . ' microseconds'))
                    )
                );
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}