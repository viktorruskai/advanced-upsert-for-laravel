# Advanced Upsert for Laravel
![GitHub release (latest by date)](https://img.shields.io/github/v/release/viktorruskai/advanced-upsert-for-laravel)
[![PHPUnit](https://github.com/viktorruskai/advanced-upsert-for-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/viktorruskai/advanced-upsert-for-laravel/actions/workflows/tests.yml)
[![PHPStan](https://github.com/viktorruskai/advanced-upsert-for-laravel/actions/workflows/phpstan.yml/badge.svg)](https://github.com/viktorruskai/advanced-upsert-for-laravel/actions/workflows/phpstan.yml)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](https://github.com/viktorruskai/advanced-upsert-for-laravel/blob/master/LICENSE)

This feature is **for now only** available for PostgreSQL (in your `.env` file `DB_CONNECTION` must be set to `pgsql`).
Upsert, based on [Wiktionary](https://en.wiktionary.org/wiki/upsert), is _an operation that inserts rows into a database table if they do not already exist, or updates them if they do_. 
The Advanced upsert is basically the same as Laravel's upsert function, but it has one key advantage. It can fetch foreign key (id) when performing an upsert.

## ⚡️️ Installation

```bash
$ composer require viktorruskai/advanced-upsert-for-laravel
```

## ⚙️ Usage

1. Add `use HasUpsert;` in your Laravel Eloquent model (make sure you have correct namespace)
2. You can use it in two ways:
    - **Normal upsert**
       ```php
       ItemAction::upsert(
           [
               [
                   'itemId' => 1,
                   'actionName' => 'Purchased',
                   'actionDescription' => 'Test description',
                   'actionValue' => 12,
               ],
               // ... (more items) 
           ], 
           ['itemId', 'actionName'], // Conflict (either columns or key name)
           ['actionDescription'] // Update column 
       );
       ```
      _Generated SQL:_
      ```sql
      INSERT INTO
          "itemActions" ("itemId", "actionName", "actionDescription", "actionValue", "updatedAt", "createdAt")
      VALUES
          (1, 'Purchased', 'Test description', 12, NOW(), NOW())
          /** ... (more items) */
      ON CONFLICT ("itemId", "actionName") 
      DO UPDATE SET
          "actionDescription" = "excluded"."actionDescription"
      ```
    - **Upsert with selecting foreign ID from different table**
        ```php
        ItemActionAdditional::upsert(
            [
                [
                    'where' => [
                        'itemId' => 1,
                        'actionName' => 'Test',
                    ],
                    'upsert' => [
                        'itemActionId' => '*' // Must be set `*`, this ID will be automatically added from `$selectModelClassName` by conditions from `where` param  
                        'specialData' => '123456',
                        'description' => 'Hello',
                    ], 
                ],
                // ... (more items)
            ], 
            ['itemActionId', 'specialData'], // Must be set as unique key (name of columns must be presented or name of the key) 
            ['description'], // Columns that will be updated
            ItemAction::class, // Eloquent model, in this case must be set
            [...] // Any columns that should be returned (Not required) 
        );
        ```
        _Generated SQL:_
        ```sql
        INSERT INTO
            "itemActionAdditional" ("itemActionId", "specialData", "description", "updatedAt", "createdAt")
            (
                SELECT
                    id,
                    '123456',
                    'Hello',
                    NOW(),
                    NOW()
                FROM
                    "itemActions"
                WHERE
                    "itemId" = 1 AND 
                    "actionName" = 'Test'
            )
            /** ... (more items) */ 
        ON CONFLICT ("itemActionId", "specialData")
        DO UPDATE SET
            "description" = "excluded"."description"
        ```
      
## 🌍 Examples
Check the `tests/Support/Tests/` folder for more examples. 

## ⚖️ Licence
Content of this package is open-sourced code licensed under the [MIT license]((https://github.com/viktorruskai/advanced-upsert-for-laravel/blob/master/LICENSE)).