<?php

namespace Aqamarine\LaravelQueryCache;

use Exception;
use RuntimeException;

trait QueryCacheModule
{
    protected  $cacheTime;

    protected $cacheTags = null;

    protected $cacheDriver;

    protected $cachePrefix = 'leqc';

    protected $cacheUsePlainKey = false;

    protected $avoidCache = true;

    public function getFromQueryCache(string $method = 'get', $columns = ['*'], $id = null)
    {
        if (is_null($this->columns)) {
            $this->columns = $columns;
        }

        $key = $this->getCacheKey();
        $cache = $this->getCache();
        $callback = $this->getQueryCacheCallback($method, $columns);
        $time = $this->getCacheTime();

        if ($time > 0) {
            return $cache->remember($key, $time, $callback);
        }

        return $cache->rememberForever($key, $callback);
    }

    public function getQueryCacheCallback(string $method = 'get', $columns = ['*'])
    {
        return function () use ($method, $columns) {
            $this->avoidCache = true;

            return $this->{$method}($columns);
        };
    }

    public function getCacheKey(string $method = 'get', $id = null, $appends = null)
    {
        $key = $this->generateCacheKey($method, $id, $appends);
        $prefix = $this->getCachePrefix();

        return "$prefix:$key";
    }

    public function generateCacheKey(string $method = 'get', $id = null, $appends = null)
    {
        $key = $this->generatePlainCacheKey($method, $id, $appends);

        if ($this->shouldUsePlainKey()) {
            return $key;
        }

        return hash('sha256', $key);
    }

    public function generatePlainCacheKey(string $method = 'get', $id = null, $appends = null): string
    {
        $name = $this->connection->getName();

        // Count has no Sql, that's why it can't be used ->toSql()
        if ($method === 'count') {
            return $name . $method . $id . serialize($this->getBindings()) . $appends;
        }

        return $name . $method . $id . $this->toSql() . serialize($this->getBindings()) . $appends;
    }

    /**
     * @throws Exception
     */
    public function flushQueryCache(array $tags = [])
    {
        $cache = $this->getCacheDriver();

        if (!count($tags)) {
            $tags = $this->getCacheTags();
        }

        if (!method_exists($cache, 'tags')) {
            throw new RuntimeException("Current cache driver doesn't support tags.");
        }

        foreach ($tags as $tag) {
            $this->flushQueryCacheWithTag($tag);
        }

        return true;
    }

    public function flushQueryCacheWithTag(string $tag)
    {
        $cache = $this->getCacheDriver();

        if (!method_exists($cache, 'tags')) {
            return false;
        }

        return $cache->tags($tag)->flush();
    }

    public function cacheFor($time)
    {
        $this->cacheTime = $time;
        $this->avoidCache = false;

        return $this;
    }

    public function cacheForever()
    {
        return $this->cacheFor(-1);
    }

    public function dontCache()
    {
        $this->avoidCache = true;

        return $this;
    }

    public function doNotCache()
    {
        return $this->dontCache();
    }

    public function cachePrefix(string $prefix)
    {
        $this->cachePrefix = $prefix;

        return $this;
    }

    public function cacheTags(array $cacheTags = [])
    {
        $this->cacheTags = $cacheTags;

        return $this;
    }

    public function cacheDriver(string $cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;

        return $this;
    }

    public function withPlainKey()
    {
        $this->cacheUsePlainKey = true;

        return $this;
    }

    public function getCacheDriver()
    {
        return isset($this->cacheDriver)
            ? app('cache')->driver($this->cacheDriver)
            : app('cache')->driver(config('cache.default'));
    }

    public function getCache()
    {
        $cache = $this->getCacheDriver();
        $tags = $this->getCacheTags();

        return $tags ? $cache->tags($tags) : $cache;
    }

    public function shouldAvoidCache()
    {
        return $this->avoidCache;
    }

    public function shouldUsePlainKey()
    {
        return $this->cacheUsePlainKey;
    }

    public function getCacheTime()
    {
        return $this->cacheTime;
    }

    public function getCacheTags()
    {
        return $this->cacheTags ?? [$this->tableName];
    }

    public function getCachePrefix()
    {
        return $this->cachePrefix;
    }
}
