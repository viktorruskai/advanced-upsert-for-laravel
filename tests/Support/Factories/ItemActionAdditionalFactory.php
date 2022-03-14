<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\ItemActionAdditional;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ItemActionAdditionalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ItemActionAdditional::class;

    /**
     * Define the model's default state.
     *
     * @throws Exception
     */
    public function definition(): array
    {
        return [
            'itemActionId' => $this->faker->randomNumber(2),
            'specialData' => Str::random(random_int(5, 20)),
            'description' => $this->faker->text(20),
        ];
    }
}
