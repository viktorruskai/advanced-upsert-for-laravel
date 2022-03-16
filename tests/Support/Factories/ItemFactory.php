<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Item;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @throws Exception
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => Str::random(random_int(5, 20)),
        ];
    }
}
