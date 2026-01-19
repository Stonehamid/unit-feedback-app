<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    public function definition(): array
    {
        $reportTypes = ['Performance', 'Complaint', 'Suggestion', 'Incident', 'Evaluation'];
        $priorities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['draft', 'submitted', 'in_review', 'resolved', 'rejected'];
        
        return [
            'title' => $this->faker->sentence(6),
            'content' => $this->faker->paragraphs(3, true),
            'type' => $this->faker->randomElement($reportTypes),
            'priority' => $this->faker->randomElement($priorities),
            'status' => $this->faker->randomElement($statuses),
            'created_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ];
    }
}