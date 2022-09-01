<?php declare(strict_types=1);

namespace Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration;

class DateTimeProvider
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}