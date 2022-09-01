<?php

namespace Dnomyar\PhpFileSystemRateLimiter\Domain;

class BucketRepositoryException extends \Exception
{

    public function __construct(string $cause)
    {
        parent::__construct($cause);
    }
}