<?php

namespace App\Api\Response;


use Illuminate\Cache\Repository;

/**
 * Response that is saved to cache.
 */
class CachedResponse extends Response
{
    /**
     * @var Repository
     */
    protected $cacheRepository;

    /**
     * @var string
     */
    protected $cacheKey;

    /**
     * @var \Closure
     */
    protected $buildCallback;

    /**
     * @var \DateTimeInterface|\DateInterval|float|int
     */
    protected $rememberMinutes;

    /**
     * @inheritDoc
     *
     * @param string                                     $success
     * @param string                                     $cacheKey
     * @param \Closure                                   $buildCallback
     * @param Repository                                 $cache
     * @param \DateTimeInterface|\DateInterval|float|int $rememberMinutes
     */
    public function __construct(
        $success,
        $cacheKey,
        $buildCallback,
        Repository $cache,
        $rememberMinutes = null
    ) {
        $this->cacheRepository = $cache;
        $this->cacheKey        = $cacheKey;
        $this->buildCallback   = $buildCallback;

        parent::__construct($success, null);
    }

    /**
     * See Repository::remember.
     *
     * If `$callback` and `$as` are null, uses the CachedResponse::$buildCallback and saves/retrieves as
     * the main response content. Otherwise, saves as an entry that is derived from the main response content.
     *
     * @param \Closure|null $callback
     * @param string|null   $as
     *
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function remember($callback = null, $as = null)
    {
        $isMain = ($callback === null && $as === null);

        $contentKey = "api_response_{$this->cacheKey}";
        $thisKey    = $isMain ? $contentKey : "{$contentKey}_{$as}";

        if ($isMain) {
            $callback = $this->buildCallback;

            if ($this->content !== null
                && $this->cacheRepository->has($thisKey)
            ) {
                return $this->content;
            }
        } else {
            if ( ! $this->cacheRepository->has($contentKey)) {
                $this->cacheRepository->delete($thisKey);
                $this->remember();
            }
        }

        $result = null;
        if ($this->rememberMinutes !== null) {
            $result = $this->cacheRepository->remember($thisKey,
                $this->rememberMinutes, $callback);
        } else {
            $result = $this->cacheRepository->rememberForever($thisKey,
                $callback);
        }

        if ($isMain) {
            $this->content = $result;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function getContent()
    {
        return $this->remember();
    }

    /**
     * @inheritDoc
     */
    public function toJson($options = 0)
    {
        return $this->remember(function () use ($options) {
            return parent::toJson($options);
        }, 'JSON');
    }
}