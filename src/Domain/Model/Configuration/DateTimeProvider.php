<?php declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration;

class DateTimeProvider
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}