# Laravel Query Cache

Based on [miradnan/laravel-model-caching](https://github.com/miradnan/laravel-model-caching) package which stores cache for database query and automatically cleans it
after Creating or Updating Laravel model.

## Installation

Using the package manager [composer](https://getcomposer.org).

```bash
$ composer require aqamarine228/laravel-query-cache
```

## Usage

##### for package to work your cache driver has to support 'tags'

Add QueryCacheable trait to make model cachable

```php
use Aqamarine228\LaravelQueryCache\QueryCacheable;

class Categories extends Model
{
    use QueryCacheable;

    ...
}
```

In order to cache all model queries you need to set '$cacheFor' model property

```php
public $cacheFor = 3600;
```

Or you can cache only specific queries as in example below

```php
$category = Category::cacheFor(60 * 60)->first();

// Using a DateTime instance like Carbon works perfectly fine!
$category = Category::cacheFor(now()->addDays(1))->first();
```

### Cache Tags

This package automatically adds table name tag to every caching query, but you can customise this behavior by setting
'cacheTags' model property

```php
public $cacheTags = ['customTag'];
```

It will replace every caching query default tag with property value.

Alternately you can use tags as it says in [miradnan/laravel-model-caching](https://github.com/miradnan/laravel-model-caching) README


##### if used in builder query, as in example below, cache won't be cleaned automatically

```php
$shelfOneBooks = Book::whereShelf(1)->cacheFor(60)->cacheTags(['shelf:1'])->get();
```

##### so you need to clean it manually

```php
Book::flushQueryCache(['shelf:1']);
```

### Relationship Caching, Cache Keys, Cache Drivers, Disable caching & Equivalent Methods and Variables

Topics are the same as in [miradnan/laravel-model-caching](https://github.com/miradnan/laravel-model-caching) README.

### Customisation

Because in this package is used only some parts of [miradnan/laravel-model-caching](https://github.com/miradnan/laravel-model-caching)
package all customisation possibilities are cut off

## Possible conflicts

Package QueryCacheable Trait rewrites deleted and saved model events and uses custom CacheBuilder.
So every package that does the same thing will conflict. In order to avoid conflict you should use
[miradnan/laravel-model-caching](https://github.com/miradnan/laravel-model-caching) package because of it's better flexibility

## License
[MIT](https://choosealicense.com/licenses/mit/)