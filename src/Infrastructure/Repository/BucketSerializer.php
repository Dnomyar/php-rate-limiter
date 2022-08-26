<?php

namespace Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository;

use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Bucket;

interface BucketSerializer
{
    public function serialize(Bucket $bucket): string;

    public function deserialize(string $serializeBucket): ?Bucket;
}

