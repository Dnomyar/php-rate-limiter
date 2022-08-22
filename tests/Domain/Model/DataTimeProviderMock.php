<?php declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Test\Domain\Model;

use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\DateTimeProvider;

class DataTimeProviderMock extends DateTimeProvider
{

    private \DateTime $dateTime;

    /**
     * @param \DateTime $dateTime
     */
    public function setDateTime(\DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    public function now(): \DateTime
    {
        return $this->dateTime;
    }

}