<?php declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration;

class BucketSize
{

    private int $initialSize;
    private int $currentSize;

    // this constructor should only be used to rehydrate the object
    public function __construct(int $initialSize, int $currentSize)
    {
        $this->initialSize = $initialSize;
        $this->currentSize = $currentSize;
    }

    public static function createBucketSize(int $initialSize): self{
        return new BucketSize($initialSize, $initialSize);
    }

    /**
     * @return int
     */
    public function getCurrentSize(): int
    {
        return $this->currentSize;
    }

    /**
     * @return int
     */
    public function getInitialSize(): int
    {
        return $this->initialSize;
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