<?php

namespace App\Services;

class HolidayModeService
{
    /**
     * Check if holiday mode is active.
     */
    public static function isEnabled(): bool
    {
        return (bool) config('holiday.enabled', false);
    }

    /**
     * Check if Diwali Diya graphic should be displayed.
     */
    public static function showDiya(): bool
    {
        return (bool) config('holiday.show_diya', true);
    }

    /**
     * Get holiday mode popup/banner title.
     */
    public static function title(): string
    {
        return (string) config('holiday.title', __('Happy Diwali from Devbhoomi Naturals! 🪔'));
    }

    /**
     * Get holiday mode notification message.
     */
    public static function message(): string
    {
        return (string) config('holiday.message', __('Wishing you and your family a joyful, healthy, and prosperous Diwali! Our team is taking a short festival break. You can explore all our natural Himalayan products and add them to your cart, but checkout and dispatch will resume right after the festivities!'));
    }
}
