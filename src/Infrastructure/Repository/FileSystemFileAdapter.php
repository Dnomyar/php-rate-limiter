<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository;

interface FileSystemFileAdapter
{
    public function get(string $filename): ?string;

    public function save(string $filename, string $toSave): void;
}

