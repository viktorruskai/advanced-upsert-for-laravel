<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Item
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $updatedAt
 * @property string $createdAt
 * @method static Item create(array $attributes = [])
 */
class Item extends Model
{
    use HasFactory;

    public const UPDATED_AT = 'updatedAt';
    public const CREATED_AT = 'createdAt';

    protected $table = 'items';

    protected $fillable = [
        'name',
        'description',
    ];
}
