# Advanced Upsert for Laravel

[![PHPUnit](https://github.com/viktorruskai/advanced-upsert-for-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/viktorruskai/advanced-upsert-for-laravel/actions/workflows/tests.yml)
[![PHPStan](https://github.com/viktorruskai/advanced-upsert-for-laravel/actions/workflows/phpstan.yml/badge.svg)](https://github.com/viktorruskai/advanced-upsert-for-laravel/actions/workflows/phpstan.yml)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](https://github.com/viktorruskai/advanced-upsert-for-laravel/blob/master/LICENSE)

Upsert many (1 000 000+) rows in just a seconds. Also fetch a foreign key in upsert query in order to get ID to every
row.

## ⚡️️ Installation

```bash
$ composer require viktorruskai/advanced-upsert-for-laravel
```

## ⚙️ Usage

1. Add `use HasUpsert;` in your Laravel Eloquent model (make sure )
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
      Generated SQL:
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
        Generated SQL:
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


* Make sure you have correct namespace