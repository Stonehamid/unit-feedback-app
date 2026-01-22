<?php

namespace Database\Factories;

use App\Models\Rating;
use App\Models\RatingCategory;
use App\Models\RatingScore;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingScoreFactory extends Factory
{
    protected $model = RatingScore::class;

    public function definition()
    {
        return [
            'rating_id' => Rating::factory(),
            'rating_category_id' => RatingCategory::factory(),
            'skor' => $this->faker->randomFloat(1, 1, 5),
        ];
    }

    public function skorTinggi()
    {
        return $this->state([
            'skor' => $this->faker->randomFloat(1, 4, 5),
        ]);
    }

    public function skorRendah()
    {
        return $this->state([
            'skor' => $this->faker->randomFloat(1, 1, 2.5),
        ]);
    }
}