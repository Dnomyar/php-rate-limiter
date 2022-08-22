<?php
declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Domain;

use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Bucket;

interface BucketRepository {

    function get(string $id): ?Bucket;
    function save(string $id, Bucket $bucket);

    function upsert(string $id, callable $update): void;

}