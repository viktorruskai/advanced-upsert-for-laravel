<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ViktorRuskai\AdvancedUpsert\HasUpsert;

/**
 * Class ItemAction
 *
 * @package App\Models
 * @property int $id
 * @property int $itemId
 * @property string $actionName
 * @property string $actionDescription
 * @property double|null $actionValue
 * @property string $updatedAt
 * @property string $createdAt
 * @method static Builder where($column, $values, $boolean = 'and', $not = false)
 * @method static Builder select($query, $bindings = [], $useReadPdo = true)
 */
class ItemAction extends Model
{
    use HasFactory, HasUpsert;

    public const UPDATED_AT = 'updatedAt';
    public const CREATED_AT = 'createdAt';

    protected $table = 'itemActions';

    protected $fillable = [
        'itemId',
        'actionName',
        'actionDescription',
        'actionValue',
    ];
}
