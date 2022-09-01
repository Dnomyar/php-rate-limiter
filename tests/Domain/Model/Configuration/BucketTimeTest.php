<?php

namespace Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Configuration;

use Dnomyar\PhpFileSystemRateLimiter\Test\Domain\Model\DateTimeProviderMock;
use PHPUnit\Framework\TestCase;

class BucketTimeTest extends TestCase
{

    public function testThatIntervalIsNotElapsedIfDataTimeIsNowAndDurationIs10s()
    {
        $dataTimeProviderMock = new DateTimeProviderMock();
        $dataTimeProviderMock->setDateTime(new \DateTimeImmutable());
        $bucketTime = new BucketTime(
            Duration::seconds(10),
            $dataTimeProviderMock
        );
        $this->assertFalse($bucketTime->intervalIsElapsed());
    }

    public function testThatIntervalIsElapsedWhenTheDurationIs10sAndTheDateTimeIs11sInThePast(){
        $dataTimeProviderMock = new DateTimeProviderMock();
        $dataTimeProviderMock->setDateTime(
            (new \DateTimeImmutable())->sub(\DateInterval::createFromDateString('15 seconds'))
        );
        $bucketTime = new BucketTime(
            Duration::seconds(10),
            $dataTimeProviderMock
        );
        $dataTimeProviderMock->setDateTime(new \DateTimeImmutable());
        $this->assertTrue($bucketTime->intervalIsElapsed());

    }
}
