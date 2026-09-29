<?php

namespace App\Http\Requests\Admin;

use App\Services\Courier\CourierManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCourierShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $drivers = array_keys(app(CourierManager::class)->availableDrivers());

        return [
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer'],
            'courier' => ['required', 'string', Rule::in($drivers)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'order_ids.required' => 'Select at least one order.',
            'courier.in' => 'The selected courier is not available.',
        ];
    }
}
