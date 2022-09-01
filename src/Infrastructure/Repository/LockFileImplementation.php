<?php

namespace Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository;

class LockFileImplementation implements LockFile
{

    public function lock(string $file): bool
    {
        try {
            return mkdir($this->lockedFileName($file));
        } catch (\Exception) {
            return false;
        }
    }

    public function unlock(string $file): void
    {
        try {
            unlink($this->lockedFileName($file));
        } catch (\Exception) {
        }
    }

    /**
     * @param string $file
     * @return string
     */
    public function lockedFileName(string $file): string
    {
        return $file . '.lock';
    }
}