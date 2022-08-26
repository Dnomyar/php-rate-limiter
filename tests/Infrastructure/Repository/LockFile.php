<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Test\Infrastructure\Repository;

interface LockFile
{
    public function lock(string $file): void;

    public function unlock(string $file): void;
}