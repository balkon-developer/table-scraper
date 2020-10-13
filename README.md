<p align="center">
    <a href="https://travis-ci.org/mrofi/table-scraper">
        <img src="https://travis-ci.org/mrofi/table-scraper.svg?branch=dev" alt="Build Status">
    </a>
    <a href="https://codecov.io/gh/mrofi/table-scraper" title="Code coverage">
        <img alt="Codecov Code Coverage" src="https://img.shields.io/codecov/c/github/mrofi/table-scraper?logo=codecov">
    </a>
</p>

# PHP Table Scraper
a package to help you scraping one or some tables in a website and automatically unmerge the cells. 
The result, you get the data as Collection. It's useful for me, I hope it will helpful for you so.

## Installation
```
composer require balkon-developer/table-scraper
```

## How to use
- Get a table with css selector, the result is `ScrapeTable` class
```php
use BalkonDeveloper\TableScraper\Scrape;

$url = 'https://your-target-website';
$scrape = Scrape::fromUrl($url);

$tableSelector = '.target';
$table = $scrape->table($tableSelector);

// var_dump($table)
```

- Get multiple tables with css selector, the result is `Collection` of `ScrapeTable` class
```php
use BalkonDeveloper\TableScraper\Scrape;

$url = 'https://your-target-website';
$scrape = Scrape::fromUrl($url);

$tableSelector = '.target';
$tables = $scrape->tables($tableSelector);

// var_dump($tables)
```

- Get all tables, the result is `Collection` of `ScrapeTable` class
```php
use BalkonDeveloper\TableScraper\Scrape;

$url = 'https://your-target-website';
$scrape = Scrape::fromUrl($url);

$tables = $scrape->tables();

// var_dump($tables)
```

## Result as ScrapeTable


## Result as Collection


## Helpers
