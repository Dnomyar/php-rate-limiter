<?php declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Test\Domain\Model;

use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\DateTimeProvider;
use DateTimeImmutable;

class DateTimeProviderMock extends DateTimeProvider
{

    private DateTimeImmutable $dateTime;

    /**
     * @param DateTimeImmutable $dateTime
     */
    public function setDateTime(DateTimeImmutable $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    public function now(): DateTimeImmutable
    {
        return $this->dateTime;
    }

}