<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Viktorruskai\AdvancedUpsert\UpsertQuery;

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
 */
class ItemAction extends Model
{
    use HasFactory, UpsertQuery;

    public const UPDATED_AT = 'updatedAt';
    public const CREATED_AT = 'createdAt';

    protected $table = 'itemActions';

    protected $fillable = [
        'externalId',
        'instagramId',
        'name',
        'username',
        'followersCount',
        'followsCount',
        'mediaCount',
        'picture',
    ];
}
