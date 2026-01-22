<?php

return [
    'guest' => [
        'max_attempts' => 100,
        'decay_minutes' => 1,
    ],
    
    'auth' => [
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],
    
    'rating' => [
        'max_per_day_per_unit' => 1,
        'cooldown_hours' => 24,
    ],
    
    'report' => [
        'max_per_day' => 5,
        'decay_minutes' => 1440,
    ],
];