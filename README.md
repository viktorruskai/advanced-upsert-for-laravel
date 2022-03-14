# Advanced Upsert for Laravel

[![License](https://img.shields.io/badge/License-MIT-green.svg)](https://github.com/viktorruskai/advanced-upsert-for-laravel/blob/master/LICENSE)
[![PHPStan](https://github.com/viktorruskai/advanced-upsert-for-laravel/actions/workflows/phpstan.yml/badge.svg)](https://github.com/viktorruskai/advanced-upsert-for-laravel/actions/workflows/phpstan.yml)

Upsert many (1 000 000+) rows in just a seconds. Also fetch a foreign key in upsert query in order to get ID to every
row.

## ⬇️ Installation

```bash
$ composer require viktorruskai/advanced-upsert-for-laravel
```

## ⚙️ Usage

1. Add `use UpsertQuery;` in your Laravel Eloquent model (*)
2. You can use it in two ways:
    1. Normal upsert
       ```php
       ItemAction::upsert([
         [
             'itemId' => 1,
             'actionName' => 'purchased',
             'actionDescription' => 'test',
             'actionValue' => 12,
         ],
         [ 
             'itemId' => 2,
             'actionName' => 'cancelled',
             'actionDescription' => 'topic',
             'actionValue' => 153,
         ],
         ...
       ]);
       ```
    2. With selecting foreign ID while upserting
       ```php
       ItemActionAdditional::upsert([
             [
                 'where' => [
                     'actionName' => 'Test',
                     ...
                 ],
                 'upsert' => [
                     'itemActionId' => '*' // Must be set `*`, this ID will be automatically added from `$selectModelClassName` by conditions from `where` param  
                     'specialData' => ...
                 ] 
             ],
             [
                 'where' => ...
                 'upsert' => ...
             ],
             ...
         ], 
         ['itemId', 'actionName'], // Must be set as unique key 
         ['specialData', 'updatedAt'], // Columns that will be updated
         ItemAction::class, // Eloquent model, in this case must be set
         [...] // Any columns that should be returned (Not required) 
       );
       ```


* Make sure you have correct namespace