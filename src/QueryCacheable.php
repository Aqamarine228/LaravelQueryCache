<?php

namespace Aqamarine\LaravelQueryCache;

trait QueryCacheable
{
    public static function boot()
    {
        parent::boot();

        static::saved(static function () {
            self::flushQueryCache();
        });

        static::deleted(static function () {
            self::flushQueryCache();
        });
    }

    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        $builder = new CacheBuilder(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor(),
            $this->getTable(),
        );

        if ($this->cacheFor) {
            $builder->cacheFor($this->cacheFor);
        } else {
            $builder->dontCache();
        }

        if ($this->cacheTags) {
            $builder->cacheTags($this->cacheTags);
        }

        if ($this->cachePrefix) {
            $builder->cachePrefix($this->cachePrefix);
        }

        if ($this->cacheDriver) {
            $builder->cacheDriver($this->cacheDriver);
        }

        if ($this->cacheUsePlainKey) {
            $builder->withPlainKey();
        }

        return $builder;
    }
}
