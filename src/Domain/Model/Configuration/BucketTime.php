<?php
declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration;

class BucketTime
{
    private Duration $duration;
    private \DateTime $timeLastReset;
    private DateTimeProvider $dateTimeProvider;

    public function __construct(Duration $duration, ?DateTimeProvider $dateTimeProvider = null)
    {
        $this->duration = $duration;
        $this->dateTimeProvider = $dateTimeProvider ?? new DateTimeProvider();
        $this->timeLastReset = $this->dateTimeProvider->now();
    }

    public function intervalIsElapsed(): bool
    {
        $now = $this->dateTimeProvider->now();

        // TODO: is there a better way to do that?
        $interval = \DateInterval::createFromDateString($this->duration->getSeconds() . ' seconds');

        return $this->timeLastReset->add($interval)->getTimestamp() < $now->getTimestamp();
    }

    public function reset(): BucketTime
    {
        return new BucketTime(
            $this->duration,
            $this->dateTimeProvider
        );
    }
}