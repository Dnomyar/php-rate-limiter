<?php
declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Test\Domain;

use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\Duration;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use \Damienraymond\PhpFileSystemRateLimiter\Domain\RateLimiter;
use Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository\InMemoryBucketRepository;
use Damienraymond\PhpFileSystemRateLimiter\Test\Domain\Model\DateTimeProviderMock;
use PHPUnit\Framework\TestCase;

class RateLimiterTest extends TestCase
{

    private string $id = 'test';

    public function test()
    {
        $rateLimiter = $this->createRateLimiter();
        $this->assertInstanceOf('\Damienraymond\PhpFileSystemRateLimiter\Domain\RateLimiter', $rateLimiter);
    }

    public function testThatRateLimiterAllows()
    {
        $rateLimiter = $this->createRateLimiter();
        $this->assertTrue($rateLimiter->allowCall($this->id));
    }

    public function testThatAllowCallReturnsTrueForBucketSizeNumberOfCalls()
    {
        $rateLimiter = $this->createRateLimiter();
        for ($i = 0; $i < 9; $i++) {
            $this->assertTrue($rateLimiter->allowCall($this->id));
        }
    }

    public function testThatRateLimiterBlocksIfTheTooManyRequestsHaveBeenMade()
    {
        $rateLimiter = $this->createRateLimiter();
        for ($i = 0; $i < 15; $i++) {
            $rateLimiter->allowCall($this->id);
        }
        $this->assertFalse($rateLimiter->allowCall($this->id));
    }

    public function testThatThatBucketGetsResetOnceTheThresholdWasReached()
    {
        $dataTimeProviderMock = new DateTimeProviderMock();
        $now = new \DateTimeImmutable();
        $dataTimeProviderMock->setDateTime($now);
        $bucketTime = new BucketTime(Duration::seconds(10), $dataTimeProviderMock);
        $rateLimiter = new RateLimiter(
            $bucketTime,
            BucketSize::createBucketSize(6),
            new InMemoryBucketRepository()
        );

        for ($i = 1; $i < 6; $i++) {
            $this->assertTrue($rateLimiter->allowCall($this->id));
        }
        $this->assertFalse($rateLimiter->allowCall($this->id));

        $nowPlus65Seconds = (new \DateTimeImmutable())->add(\DateInterval::createFromDateString('11 seconds'));
        $dataTimeProviderMock->setDateTime($nowPlus65Seconds);

        $this->assertTrue($rateLimiter->allowCall($this->id));

    }

    /**
     * @return RateLimiter
     */
    public function createRateLimiter(): RateLimiter
    {
        return new RateLimiter(
            new BucketTime(Duration::seconds(60)),
            BucketSize::createBucketSize(10),
            new InMemoryBucketRepository()
        );
    }
}