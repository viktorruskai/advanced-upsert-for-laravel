<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\ItemAction;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemActionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ItemAction::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'itemId' => $this->faker->randomNumber(2),
            'actionName' => $this->faker->word(),
            'actionDescription' => $this->faker->text(),
            'actionValue' => $this->faker->numberBetween(0, 10000),
        ];
    }
}
