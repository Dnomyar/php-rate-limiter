# Low budget rate limiter

This is a low budget rate limiter. Low budget because it uses the file system as a repository: it does not require another other tool. It can be used to protect a public form from bots or limit by user.


## Usage
This library provides a `RateLimiter` class that need to be initialized with a identificator corresponding to the resources that need to be limited. For instance:

- limit the number of submitions to a public page. In that case, the id could be `business-description`
- limit the number of call per user. In that case, the id could be `business-description-<user-id>`

```php
use Damienraymond\PhpFileSystemRateLimiter\Domain\RateLimiter;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketSize;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\BucketTime;
use Damienraymond\PhpFileSystemRateLimiter\Domain\Model\Configuration\Duration;
use Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository\LockFileImplementation;
use Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository\BucketSerializerImplementation;
use Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository\FileSystemFileAdapterImplementation;
use Damienraymond\PhpFileSystemRateLimiter\Infrastructure\Repository\FileSystemBucketRepository;

$rateLimiter = new RateLimiter(
    'id-to-change',
     new BucketTime(Duration::seconds(10)),
     BucketSize::createBucketSize(6),
     new FileSystemBucketRepository(
        new FileSystemFileAdapterImplementation(),
        new BucketSerializerImplementation(),
        new LockFileImplementation()
     )
);

/*
 * return true of false if the call is allowed
 * thows a BucketRepositoryException if another request is trying to use the feature at the same time.
 */
$rateLimiter->allowCall();
```

## Limitation
If you are looking to use that for your project, use caution. This library was built to respect two important constraints: not using additionnal tool and a low level of parallel request for the same identificator.

Also, it would cause problems in case of a pick of load. It is advise to run load testing before using this library.