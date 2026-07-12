<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VariantLabel;
use App\Models\VariantOption;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VariantLabelAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:'.(\App\Models\User::ROLE_ADMIN)]);
    }

    public function index()
    {
        $labels = VariantLabel::with('options')->orderBy('name')->get();
        return view('admin.variant-labels.index', compact('labels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:64|unique:variant_labels,name',
            'options' => 'nullable|string', // Comma separated options on initial creation
        ]);

        $label = VariantLabel::create([
            'name' => trim($data['name']),
        ]);

        if (! empty($data['options'])) {
            $optionsArray = array_filter(array_map('trim', explode(',', $data['options'])));
            foreach ($optionsArray as $val) {
                VariantOption::firstOrCreate([
                    'variant_label_id' => $label->id,
                    'value' => $val,
                ]);
            }
        }

        return redirect()->route('admin.variant-labels.index')->with('status', __('Variant label created.'));
    }

    public function update(Request $request, VariantLabel $variantLabel)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:64',
                Rule::unique('variant_labels', 'name')->ignore($variantLabel->id),
            ],
        ]);

        $variantLabel->update([
            'name' => trim($data['name']),
        ]);

        return redirect()->route('admin.variant-labels.index')->with('status', __('Variant label updated.'));
    }

    public function destroy(VariantLabel $variantLabel)
    {
        $variantLabel->delete();
        return redirect()->route('admin.variant-labels.index')->with('status', __('Variant label deleted.'));
    }

    public function storeOption(Request $request, VariantLabel $variantLabel)
    {
        $data = $request->validate([
            'value' => 'required|string|max:64',
        ]);

        $value = trim($data['value']);

        // Check uniqueness for this label
        $exists = VariantOption::where('variant_label_id', $variantLabel->id)
            ->where('value', $value)
            ->exists();

        if ($exists) {
            return back()->withErrors(['value' => __('This option already exists for this label.')]);
        }

        VariantOption::create([
            'variant_label_id' => $variantLabel->id,
            'value' => $value,
        ]);

        return redirect()->route('admin.variant-labels.index')->with('status', __('Variant option added.'));
    }

    public function deleteOption(VariantOption $variantOption)
    {
        $variantOption->delete();
        return redirect()->route('admin.variant-labels.index')->with('status', __('Variant option deleted.'));
    }
}
