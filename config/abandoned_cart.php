<?php

return [
    'enabled' => (bool) env('ABANDONED_CART_REMINDERS_ENABLED', false),
    'stages' => [
        1 => max(1, (int) env('ABANDONED_CART_REMINDER_STAGE_1_MINUTES', 60)),
        2 => max(1, (int) env('ABANDONED_CART_REMINDER_STAGE_2_MINUTES', 1440)),
        3 => max(1, (int) env('ABANDONED_CART_REMINDER_STAGE_3_MINUTES', 4320)),
    ],
    'claim_timeout_minutes' => max(5, (int) env('ABANDONED_CART_REMINDER_CLAIM_TIMEOUT_MINUTES', 120)),
];
