<?php
declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration;

class BucketTime
{
    private Duration $duration;
    private \DateTimeImmutable $timeLastReset;
    private DateTimeProvider $dateTimeProvider;

    public function __construct(Duration $duration, ?DateTimeProvider $dateTimeProvider = null, ?\DateTimeImmutable $timeLastReset = null)
    {
        $this->duration = $duration;
        $this->dateTimeProvider = $dateTimeProvider ?? new DateTimeProvider();
        $this->timeLastReset = $timeLastReset ?? $this->dateTimeProvider->now();
    }

    public function intervalIsElapsed(): bool
    {
        $now = $this->dateTimeProvider->now();

        // TODO: is there a better way to do that?
        $interval = \DateInterval::createFromDateString($this->duration->getSeconds() . ' seconds');

        $timestamp = $this->timeLastReset->add($interval)->getTimestamp();

        return $timestamp < $now->getTimestamp();
    }

    public function reset(): BucketTime
    {
        return new BucketTime(
            $this->duration,
            $this->dateTimeProvider
        );
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getTimeLastReset(): \DateTimeImmutable
    {
        return $this->timeLastReset;
    }

    /**
     * @return Duration
     */
    public function getDuration(): Duration
    {
        return $this->duration;
    }


}