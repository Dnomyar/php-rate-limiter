<?php
declare(strict_types=1);

namespace Damienraymond\PhpFileSystemRateLimiter\Domain;

use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Bucket;

interface BucketRepository {

    /**
     * The implementation of this repository need to be an atomic operation
     * Initially, I was going to have a `get` and a `save` method but making to make that atomic, I would have needed
     * something to handle a transaction in the domain which is more something related to the infrastructure
     *
     * @param string $id
     * @param callable $update
     * @param Bucket $initialBucket
     * @return Bucket
     */
    function upsert(string $id, callable $update, Bucket $initialBucket): Bucket;

}