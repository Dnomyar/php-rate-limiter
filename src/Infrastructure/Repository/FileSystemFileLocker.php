<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository;

interface FileSystemFileLocker
{
    public function lock(string $filename): bool;

    public function unlock(string $filename): void;
}