<?php declare(strict_types=1);

namespace Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration;

class Duration
{
    private int $seconds;

    private function __construct(int $seconds)
    {
        $this->seconds = $seconds;
    }

    public static function seconds(int $seconds): Duration
    {
        return new Duration($seconds);
    }

    /**
     * @return int
     */
    public function getSeconds(): int
    {
        return $this->seconds;
    }

}