# Caching

## Exercise 1
See [LastthreeblocksBlock.php](/src/Plugin/Block/LastthreeblocksBlock.php)

## Exercise 2
See [UserArticleCategoryBlock.php](/src/Plugin/Block/UserArticleCategoryBlock.php)
See [UserCategoryCacheContext.php](/src/Cache/Context/UserCategoryCacheContext.php)

## Exercise 3

### Varnish Configuration
Used [ddev-varnish](https://github.com/ddev/ddev-varnish)
Installed [varnish purge module](https://www.drupal.org/project/varnish_purge)
Added a purger
Updated the VCL config and added the current IP [VCL config](./default.vcl)
Added custom header `X-Cache` to check Varnish hit and miss

Results:
1. ![First Page Load](/Exercise%203/first%20page%20load.png)
2. ![Second Page Load](/Exercise%203/second%20page%20load.png)
3. ![Purge](/Exercise%203/purge.png)

### Memcache Configuration
Used [ddev-memcached](https://github.com/ddev/ddev-memcached)
Installed [memcached module](https://www.drupal.org/project/memcache)
Added the following lines to `settings.php`:
```php
$settings['cache']['bins']['render'] = 'cache.backend.memcache';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.memcache';
$settings['memcache']['servers'] = ['memcached:11211' => 'default'];
```

Results:
1. ![Memcached](/Exercise%203/memcached.png)