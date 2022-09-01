<?php

namespace Dnomyar\PhpFileSystemRateLimiter\Infrastructure\Repository;

use Dnomyar\PhpFileSystemRateLimiter\Domain\Model\Bucket;

interface BucketSerializer
{
    public function serialize(Bucket $bucket): string;

    public function deserialize(string $serializeBucket): ?Bucket;
}

