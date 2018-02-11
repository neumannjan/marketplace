<?php

namespace App\Eloquent\Timestamp;


use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * {@see Paginator} based on a timestamp.
 */
class TimestampPaginator implements Paginator, Arrayable, Jsonable, \JsonSerializable
{
    /**
     * All of the items being paginated.
     *
     * @var Collection
     */
    protected $items;

    /**
     * The number of items to be shown per page.
     *
     * @var int
     */
    protected $perPage;

    /**
     * The current timestamp being "viewed".
     *
     * @var int
     */
    protected $currentTimestamp;

    /**
     * The base path to assign to all URLs.
     *
     * @var string
     */
    protected $path = '/';

    /**
     * The query parameters to add to all URLs.
     *
     * @var array
     */
    protected $query = [];

    /**
     * The URL fragment to add to all URLs.
     *
     * @var string|null
     */
    protected $fragment;

    /**
     * The query string variable used to store the timestamp.
     *
     * @var string
     */
    protected $timestampName = 'timestamp';

    /**
     * TimestampPaginator constructor.
     * @param Collection $items
     * @param int $perPage
     * @param int|null $timestamp
     */
    public function __construct(Collection $items, $perPage, $timestamp = null)
    {
        $this->items = $items;
        $this->perPage = $perPage;
        $this->currentTimestamp = $timestamp;
    }

    /**
     * Paginate a database query
     * @param Builder $query
     * @param int $perPage
     * @param int|null $timestamp
     * @return TimestampPaginator
     */
    public static function fromQuery(Builder $query, $perPage, $timestamp = null)
    {
        if ($timestamp) {
            $query->scopes(['after' => $timestamp]);
        }

        $query->limit($perPage);
        $result = $query->get();
        return new TimestampPaginator($result, $perPage, $timestamp);
    }

    /**
     * The URL for the next page, or null.
     *
     * @return string|null
     */
    public function nextPageUrl()
    {
        if ($this->hasMorePages()) {
            $last = $this->items->last();
            if ($last instanceof OrderAwareModel) {
                return $this->url($last->orderTimestamp);
            } else {
                $class = OrderAwareModel::class;
                throw new \RuntimeException("Item not instance of $class");
            }
        }

        return null;
    }

    /**
     * Get the URL for a given timestamp.
     *
     * @param Carbon|null $timestamp
     * @return string
     */
    public function url($timestamp = null)
    {
        // If we have any extra query string key / value pairs that need to be added
        // onto the URL, we will put them in query string form and then attach it
        // to the URL. This allows for extra information like sortings storage.
        $parameters = $timestamp === null ? [] : [$this->timestampName => $timestamp->timestamp];

        if (count($this->query) > 0) {
            $parameters = array_merge($this->query, $parameters);
        }

        return $this->path
            . (Str::contains($this->path, '?') ? '&' : '?')
            . http_build_query($parameters, '', '&')
            . $this->buildFragment();
    }

    /**
     * Get / set the URL fragment to be appended to URLs.
     *
     * @param  string|null $fragment
     * @return $this|string|null
     */
    public function fragment($fragment = null)
    {
        if (is_null($fragment)) {
            return $this->fragment;
        }

        $this->fragment = $fragment;

        return $this;
    }

    /**
     * Add a set of query string values to the paginator.
     *
     * @param  array|string $key
     * @param  string|null $value
     * @return $this
     */
    public function appends($key, $value = null)
    {
        if (is_array($key)) {
            return $this->appendArray($key);
        }

        return $this->addQuery($key, $value);
    }

    /**
     * Add an array of query string values.
     *
     * @param  array $keys
     * @return $this
     */
    protected function appendArray(array $keys)
    {
        foreach ($keys as $key => $value) {
            $this->addQuery($key, $value);
        }

        return $this;
    }

    /**
     * Add a query string value to the paginator.
     *
     * @param  string $key
     * @param  string $value
     * @return $this
     */
    protected function addQuery($key, $value)
    {
        if ($key !== $this->timestampName) {
            $this->query[$key] = $value;
        }

        return $this;
    }

    /**
     * Build the full fragment portion of a URL.
     *
     * @return string
     */
    protected function buildFragment()
    {
        return $this->fragment ? '#' . $this->fragment : '';
    }

    /**
     * Get the slice of items being paginated.
     *
     * @return array
     */
    public function items()
    {
        return $this->items->all();
    }

    /**
     * Set the base path to assign to all URLs.
     *
     * @param  string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Not implemented.
     */
    public function previousPageUrl()
    {
        throw new \RuntimeException("Not implemented");
    }

    /**
     * Not implemented.
     */
    public function firstItem()
    {
        throw new \RuntimeException("Not implemented");
    }

    /**
     * Not implemented.
     */
    public function lastItem()
    {
        throw new \RuntimeException("Not implemented");
    }

    /**
     * Determine how many items are being shown per page.
     *
     * @return int
     */
    public function perPage()
    {
        return (int)$this->perPage;
    }

    /**
     * Not implemented.
     */
    public function currentPage()
    {
        throw new \RuntimeException("Not implemented");
    }

    /**
     * Determine if there are enough items to split into multiple pages.
     *
     * @return bool
     */
    public function hasPages()
    {
        return true;
    }

    /**
     * Determine if there is more items in the data store.
     *
     * @return bool
     */
    public function hasMorePages()
    {
        return $this->items->count() === $this->perPage();
    }

    /**
     * Determine if the list of items is empty or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    /**
     * Determine if the list of items is not empty.
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return $this->items->isNotEmpty();
    }

    /**
     * Render the paginator using a given view.
     *
     * @param  string|null $view
     * @param  array $data
     * @return string
     */
    public function render($view = null, $data = [])
    {
        throw new \RuntimeException("Not implemented");
    }

    /**
     * Get the paginator's underlying collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCollection()
    {
        return $this->items;
    }

    /**
     * Make dynamic calls into the collection.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->getCollection()->$method(...$parameters);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'data' => $this->items->toArray(),
            'first_page_url' => $this->url(),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path,
            'per_page' => $this->perPage(),
            'count' => $this->items->count()
        ];
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}