<?php declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration;

class BucketSize
{

    private int $initialSize;
    private int $currentSize;

    private function __construct(int $initialSize, int $currentSize)
    {
        $this->initialSize = $initialSize;
        $this->currentSize = $currentSize;
    }

    public static function createBucketSize(int $initialSize){
        return new BucketSize($initialSize, $initialSize);
    }

    /**
     * @return int
     */
    public function getCurrentSize(): int
    {
        return $this->currentSize;
    }

    public function decrease(): BucketSize
    {
        return new BucketSize($this->initialSize, max(0, $this->getCurrentSize() - 1));
    }

    public function reset()
    {
        return $this::createBucketSize($this->initialSize);
    }


}