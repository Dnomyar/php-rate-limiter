<?php declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration;

class DateTimeProvider
{
    public function now(): \DateTime
    {
        return new \DateTime();
    }
}