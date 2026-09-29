<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PincodeServiceability;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PincodeServiceabilityAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $rows = PincodeServiceability::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('pincode', 'like', '%'.$q.'%')
                        ->orWhere('city', 'like', '%'.$q.'%')
                        ->orWhere('state', 'like', '%'.$q.'%')
                        ->orWhere('courier_name', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('pincode')
            ->paginate(30)
            ->withQueryString();

        return view('admin.pincode-serviceability', compact('rows', 'q'));
    }

    public function store(Request $request)
    {
        PincodeServiceability::create($this->validated($request));

        return back()->with('status', __('Pincode mapping created.'));
    }

    public function update(Request $request, PincodeServiceability $pincode)
    {
        $pincode->update($this->validated($request, $pincode));

        return back()->with('status', __('Pincode mapping updated.'));
    }

    public function destroy(PincodeServiceability $pincode)
    {
        $pincode->delete();

        return back()->with('status', __('Pincode mapping deleted.'));
    }

    public function toggle(PincodeServiceability $pincode)
    {
        $pincode->update(['status' => ! $pincode->status]);

        return back()->with('status', __('Serviceability status updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?PincodeServiceability $row = null): array
    {
        $data = $request->validate([
            'pincode' => [
                'required',
                'digits:6',
                Rule::unique('pincode_serviceabilities', 'pincode')->ignore($row?->id),
            ],
            'city' => 'required|string|max:120',
            'state' => 'required|string|max:120',
            'status' => 'required|boolean',
            'day_offset' => 'required|integer|min:0|max:30',
            'courier_name' => 'nullable|string|max:120',
        ]);

        $data['pincode'] = PincodeServiceability::normalizePincode($data['pincode']);
        $data['status'] = (bool) $data['status'];

        return $data;
    }
}
