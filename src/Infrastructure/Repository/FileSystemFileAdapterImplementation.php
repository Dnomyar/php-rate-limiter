<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository;

class FileSystemFileAdapterImplementation implements FileSystemFileAdapter
{

    public function get(string $filename): ?string
    {
        try {
            return file_get_contents($filename);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function save(string $filename, string $toSave): void
    {
        file_put_contents($filename, $toSave);
    }
}