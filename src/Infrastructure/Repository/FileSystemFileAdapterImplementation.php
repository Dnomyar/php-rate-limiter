<?php

namespace Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository;

class FileSystemFileAdapterImplementation implements FileSystemFileAdapter
{

    public function get(string $filename): ?string
    {
        try {
            return file_get_contents($filename);
        } catch (\Exception) {
            return null;
        }
    }

    public function save(string $filename, string $toSave): void
    {
        try {
            file_put_contents($filename, $toSave);
        } catch (\Exception) {
        }
    }
}