<?php

return [
    'enabled' => (bool) env('ABANDONED_CART_REMINDERS_ENABLED', false),
    'delay_minutes' => max(1, (int) env('ABANDONED_CART_REMINDER_DELAY_MINUTES', 60)),
    'claim_timeout_minutes' => max(5, (int) env('ABANDONED_CART_REMINDER_CLAIM_TIMEOUT_MINUTES', 120)),
];
