<?php

namespace Aqamarine\LaravelQueryCache;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;

class CacheBuilder extends BaseBuilder
{
    use QueryCacheModule;

    protected string $tableName;

    public function __construct(
        ConnectionInterface $connection,
        Grammar             $grammar = null,
        Processor           $processor = null,
        string              $tableName = "bestTable"
    ) {
        parent::__construct($connection, $grammar, $processor);
        $this->tableName = $tableName;
    }

    public function get($columns = ['*'])
    {
        if (!$this->shouldAvoidCache()) {
            return $this->getFromQueryCache('get', $columns);
        }

        return parent::get($columns);
    }

    public function useWritePdo()
    {
        // Do not cache when using to write pdo for query.
        $this->dontCache();

        // Call parent method
        parent::useWritePdo();

        return $this;
    }
}
