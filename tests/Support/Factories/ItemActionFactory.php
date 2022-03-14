<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\ItemAction;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ItemActionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ItemAction::class;

    /**
     * Define the model's default state.
     *
     * @throws Exception
     */
    public function definition(): array
    {
        return [
            'itemId' => $this->faker->randomNumber(2),
            'actionName' => Str::random(random_int(5, 20)),
            'actionDescription' => $this->faker->text(50),
            'actionValue' => $this->faker->numberBetween(0, 10000),
        ];
    }
}
