<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository;

interface LockFile
{
    public function lock(string $file): bool;

    public function unlock(string $file): void;
}