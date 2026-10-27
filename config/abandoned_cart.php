<?php

return [
    'enabled' => (bool) env('ABANDONED_CART_REMINDERS_ENABLED', false),
    'stages' => [
        1 => 60,
        2 => 1440,
        3 => 4320,
    ],
    'claim_timeout_minutes' => max(5, (int) env('ABANDONED_CART_REMINDER_CLAIM_TIMEOUT_MINUTES', 120)),
];
