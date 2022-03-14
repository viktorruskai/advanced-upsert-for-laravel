<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ViktorRuskai\AdvancedUpsert\HasUpsert;

/**
 * Class ItemActionAdditional
 *
 * @package App\Models
 * @property int $itemActionId
 * @property string $specialData
 * @property string|null $description
 * @property string $updatedAt
 * @property string $createdAt
 * @method static Builder where($column, $values, $boolean = 'and', $not = false)
 */
class ItemActionAdditional extends Model
{
    use HasFactory, HasUpsert;

    public const UPDATED_AT = 'updatedAt';
    public const CREATED_AT = 'createdAt';

    protected $table = 'itemActionAdditionalData';

    protected $primaryKey = ['itemActionId', 'specialData'];

    public $incrementing = false;

    protected $fillable = [
        'itemActionId',
        'specialData',
        'description',
    ];
}
