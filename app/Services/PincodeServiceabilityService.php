<?php

namespace App\Services;

use App\Models\PincodeServiceability;
use Carbon\Carbon;

class PincodeServiceabilityService
{
    /**
     * @return array{
     *     ok: bool,
     *     serviceable: bool,
     *     pincode: string,
     *     city: ?string,
     *     state: ?string,
     *     courier_name: ?string,
     *     day_offset: ?int,
     *     eta_date: ?string,
     *     message: string
     * }
     */
    public function check(?string $pincode): array
    {
        $pin = PincodeServiceability::normalizePincode($pincode);

        if (strlen($pin) !== 6) {
            return $this->payload(false, false, $pin, __('Enter a valid 6-digit pincode.'));
        }

        $row = PincodeServiceability::query()->where('pincode', $pin)->first();
        if (! $row || ! $row->isEnabled()) {
            return $this->payload(true, false, $pin, __('Sorry, we do not deliver to this pincode yet.'));
        }

        $eta = Carbon::now()->startOfDay()->addDays(max(0, (int) $row->day_offset));
        $etaLabel = $eta->format('d M Y');
        $parts = [
            __('Delivery available to :city, :state.', ['city' => $row->city, 'state' => $row->state]),
        ];
        if (filled($row->courier_name)) {
            $parts[] = __('Courier: :name.', ['name' => $row->courier_name]);
        }
        $parts[] = __('Estimated delivery by :date.', ['date' => $etaLabel]);

        return $this->payload(true, true, $pin, implode(' ', $parts), $row, $etaLabel);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(
        bool $ok,
        bool $serviceable,
        string $pincode,
        string $message,
        ?PincodeServiceability $row = null,
        ?string $etaDate = null
    ): array {
        return [
            'ok' => $ok,
            'serviceable' => $serviceable,
            'pincode' => $pincode,
            'city' => $row?->city,
            'state' => $row?->state,
            'courier_name' => $row?->courier_name,
            'day_offset' => $row?->day_offset,
            'eta_date' => $etaDate,
            'message' => $message,
        ];
    }
}
