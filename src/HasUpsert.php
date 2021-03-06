<?php
declare(strict_types=1);

namespace ViktorRuskai\AdvancedUpsert;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait HasUpsert
{
    /**
     * Insert or update (upsert) with selecting id from another table
     * PostgreSQL query looks like:
     *      INSERT INTO {$table} {columns}
     *          SELECT {$record['upsert']} FROM {$selectModelTable} WHERE {$record['where']} UNION ALL
     *          SELECT {$record['upsert']} FROM {$selectModelTable} WHERE {$record['where']} UNION ALL
     *          ...
     *      ON CONFLICT {$onConflictColumns}
     *      DO UPDATE SET {$updateValues}
     *      RETURNING {$toReturnColumns}
     *
     * `$record['upsert']` - must contain element with '*' value -> into this element will be applied foreign id
     * If `$selectModelClassName` is NULL query will contain multiple `VALUES({$value})` instead of multiple `SELECT`
     *
     * @param array<string,mixed>|Collection $items One array ($item) contains `where` and `upsert` subarrays. The WHERE clause is built from `where` subarray and the SELECT (or VALUES, if $selectModelClassName is null) clauses are built from `upsert` subarray.
     * @param array|string $onConflict `array` -> columns, `string` -> constraint name
     * @param array<string>|Collection|null $updateValues
     * @param array<string>|Collection $toReturnColumns
     */
    public static function upsert($items, $onConflict, $updateValues, ?string $selectModelClassName = null, $toReturnColumns = []): array
    {
        /** @var Connection $connection */
        $connection = static::getConnectionResolver()->connection();
        $grammar = $connection->getQueryGrammar();
        /** @var Builder $query */
        $query = static::query()->getQuery();

        $items = Collection::make($items);
        $updateValues = $updateValues ? Collection::make($updateValues) : null;
        $toReturnColumns = $toReturnColumns instanceof Collection ? $toReturnColumns : Collection::make($toReturnColumns);

        $sql = self::compileInsert($grammar, $query, $items, $selectModelClassName);

        $sql .= ' ON CONFLICT ' . (is_array($onConflict) ? '(' . $grammar->columnize($onConflict) . ')' : 'ON CONSTRAINT ' . $onConflict);

        if ($updateValues) {
            $sql .= self::compileUpdate($updateValues, $grammar);

            if ($toReturnColumns->isNotEmpty()) {
                $sql .= self::compileReturn($toReturnColumns, $grammar);
            }
        } else {
            $sql .= ' DO NOTHING';
        }

        return DB::select($sql);
    }

    /**
     * Build insert query with select
     * Returned part of PostgreSQL query: (if $selectModelClassName is NOT NULL)
     *      INSERT INTO {$table} {columns}
     *          SELECT {$record['upsert']} FROM {$selectModelTable} WHERE {$record['where']} UNION ALL
     *          SELECT {$record['upsert']} FROM {$selectModelTable} WHERE {$record['where']} UNION ALL
     *          ...
     *  or
     *      INSERT INTO {$table} {columns}
     *          VALUES (...$records),
     *          VALUES (...$records),
     *          ...
     */
    protected static function compileInsert(Grammar $grammar, Builder $query, Collection $values, ?string $selectModelClassName = null): string
    {
        $table = $grammar->wrapTable($query->from);
        $selectTableName = $selectModelClassName ? app($selectModelClassName)->getTable() : null;
        $columns = null;

        if ($selectModelClassName) {
            $selectParameters = $values->map(function ($record) use ($grammar, $selectTableName, &$columns) {
                $whereParams = $record['where'];
                $processValues = self::checkForTimestamps($record['upsert']);

                if (!$columns) {
                    $columns = $grammar->columnize(array_keys($processValues));
                }

                return '(SELECT ' . self::parseValues($processValues) . ' FROM ' . $grammar->wrapTable($selectTableName) . ' WHERE ' . self::parseWheres($whereParams, $grammar) . ')';
            })->implode(' UNION ALL ');
        } else {
            $selectParameters = 'VALUES ' . $values->map(function ($record) use ($grammar, &$columns) {
                    $record = self::checkForTimestamps($record);

                    if (!$columns) {
                        $columns = $grammar->columnize(array_keys($record));
                    }

                    return '(' . self::parseValues($record) . ')';
                })->implode(',');
        }

        /** @noinspection SqlNoDataSourceInspection */
        return 'INSERT INTO ' . $table . ' (' . $columns . ') ' . $selectParameters;
    }

    /**
     * Build update statement
     * Returned part of PostgreSQL query:
     *      DO UPDATE SET
     *          {$key} = {$value},
     *          {$key} = {$value},
     *          ...
     * Note: if $key is numeric (doesn't have column name) then $value is selected from INSERT clause part (excluded.$value)
     */
    protected static function compileUpdate(Collection $update, Grammar $grammar): string
    {
        return ' DO UPDATE SET ' . $update->map(function ($value, $key) use ($grammar) {
                return is_numeric($key)
                    ? $grammar->wrap($value) . ' = ' . $grammar->wrap('excluded') . '.' . $grammar->wrap($value)
                    : $grammar->wrap($key) . ' = ' . $grammar->parameter($value);
            })
                ->implode(', ');
    }

    /**
     * Build return statement
     * Returned part of PostgreSQL query:
     *      RETURNING ...$return
     */
    protected static function compileReturn(Collection $return, Grammar $grammar): string
    {
        return ' RETURNING ' . $return->map(fn($value) => $grammar->wrap($value))->implode(', ');
    }

    /**
     * Parse parameters
     */
    protected static function parseValues($values): string
    {
        /** @var HasUpsert $self */
        $self = __CLASS__;
        return collect($values)->map(function ($value) use ($self) {
            // `id` to be mapped from select query
            if ($value === '*') {
                return 'id';
            }

            return $self::parseValue($value);
        })->implode(',');
    }

    /**
     * Parse multiple where attributes
     */
    protected static function parseWheres(array $whereItems, Grammar $grammar): string
    {
        /** @var HasUpsert $self */
        $self = __CLASS__;
        return collect($whereItems)->map(function ($value, $key) use ($grammar, $self) {
            $parsedValue = $self::parseValue($value);

            return $grammar->wrapTable($key) . ($parsedValue !== 'null' ? ' = ' . $parsedValue : ' IS NULL');
        })->implode(' AND ');
    }

    /**
     * Parse value for query statement
     */
    protected static function parseValue($value = null)
    {
        // in case of "0"
        if (is_numeric($value) && !is_string($value)) {
            return (double)$value;
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_string($value)) {
            return DB::getPdo()->quote($value);
        }

        if ($value instanceof Expression) {
            return $value->getValue();
        }

        return $value;
    }

    /**
     * Wrap a single string in keyword identifiers.
     */
    protected static function wrapValue(string $value): string
    {
        if ($value !== '*') {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }

    /**
     * Check if array has timestamps
     */
    protected static function checkForTimestamps(array $items): array
    {
        if (!isset($items[self::UPDATED_AT])) {
            $items[self::UPDATED_AT] = DB::raw('NOW()');
        }

        if (!isset($items[self::CREATED_AT])) {
            $items[self::CREATED_AT] = DB::raw('NOW()');
        }

        return $items;
    }
}
