<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VendorAdminController extends Controller
{
    public function index()
    {
        $vendors = Vendor::with('user')->latest()->paginate(20);

        return view('admin.vendors', compact('vendors'));
    }

    public function create()
    {
        $defaultCommission = (int) Setting::getValue('default_commission_percent', '12');

        return view('admin.vendors.form', [
            'vendor' => null,
            'defaultCommission' => $defaultCommission,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedVendorPayload($request);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => User::ROLE_VENDOR,
                'account_status' => $data['status'] === 'approved'
                    ? User::ACCOUNT_ACTIVE
                    : User::ACCOUNT_INACTIVE,
                'email_verified_at' => now(),
            ]);

            Vendor::create([
                'user_id' => $user->id,
                'shop_name' => $data['shop_name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'city' => $data['city'],
                'state' => $data['state'],
                'status' => $data['status'],
                'commission_percent' => $data['commission_percent'],
            ]);
        });

        return redirect()
            ->route('admin.vendors.index')
            ->with('status', __('Vendor created.'));
    }

    public function edit(Vendor $vendor)
    {
        $vendor->load('user');
        $defaultCommission = (int) Setting::getValue('default_commission_percent', '12');

        return view('admin.vendors.form', compact('vendor', 'defaultCommission'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $vendor->load('user');
        $data = $this->validatedVendorPayload($request, $vendor);

        DB::transaction(function () use ($data, $vendor) {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => User::ROLE_VENDOR,
                'account_status' => $data['status'] === 'approved'
                    ? User::ACCOUNT_ACTIVE
                    : User::ACCOUNT_INACTIVE,
            ];

            if ($data['password'] !== '') {
                $userData['password'] = Hash::make($data['password']);
            }

            if ($data['status'] === 'approved' && ! $vendor->user->email_verified_at) {
                $userData['email_verified_at'] = now();
            }

            $vendor->user->update($userData);

            $vendor->update([
                'shop_name' => $data['shop_name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'city' => $data['city'],
                'state' => $data['state'],
                'status' => $data['status'],
                'commission_percent' => $data['commission_percent'],
                'meta_title' => $data['meta_title'],
                'meta_description' => $data['meta_description'],
                'meta_keywords' => $data['meta_keywords'],
                'canonical_url' => $data['canonical_url'],
                'og_image' => $data['og_image'],
            ]);
        });

        return redirect()
            ->route('admin.vendors.index')
            ->with('status', __('Vendor updated.'));
    }

    public function destroy(Vendor $vendor)
    {
        if (OrderItem::query()->where('vendor_id', $vendor->id)->exists()) {
            return back()->with('error', __('This vendor cannot be deleted because they have order history.'));
        }

        $user = $vendor->user;

        DB::transaction(function () use ($vendor, $user) {
            $vendor->delete();
            if ($user && ! $user->isAdmin()) {
                $user->delete();
            }
        });

        return redirect()
            ->route('admin.vendors.index')
            ->with('status', __('Vendor deleted.'));
    }

    public function approve(Vendor $vendor)
    {
        $vendor->update(['status' => 'approved']);
        $vendor->user?->update([
            'account_status' => User::ACCOUNT_ACTIVE,
            'email_verified_at' => $vendor->user->email_verified_at ?? now(),
        ]);

        return back()->with('status', __('Vendor approved.'));
    }

    public function reject(Vendor $vendor)
    {
        $vendor->update(['status' => 'rejected']);

        return back()->with('status', __('Vendor rejected.'));
    }

    public function updateCommission(Request $request, Vendor $vendor)
    {
        $request->validate(['commission_percent' => 'required|numeric|min:0|max:90']);
        $vendor->update(['commission_percent' => $request->commission_percent]);

        return back()->with('status', __('Commission updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedVendorPayload(Request $request, ?Vendor $vendor = null): array
    {
        $userId = $vendor?->user_id;

        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'shop_name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('vendors', 'slug')->ignore($vendor?->id),
            ],
            'description' => 'nullable|string|max:5000',
            'city' => 'nullable|string|max:120',
            'state' => 'nullable|string|max:120',
            'status' => 'required|in:pending,approved,rejected',
            'commission_percent' => 'required|numeric|min:0|max:90',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|url|max:2048',
            'og_image' => 'nullable|string|max:2048',
        ];

        if ($vendor) {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        } else {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        $slug = trim((string) ($validated['slug'] ?? ''));
        if ($slug === '') {
            $slug = $this->uniqueSlug(Str::slug($validated['shop_name']), $vendor?->id);
        }

        return [
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'password' => (string) ($validated['password'] ?? ''),
            'shop_name' => $validated['shop_name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'status' => $validated['status'],
            'commission_percent' => (int) $validated['commission_percent'],
            'meta_title' => trim((string) ($validated['meta_title'] ?? '')) ?: null,
            'meta_description' => trim((string) ($validated['meta_description'] ?? '')) ?: null,
            'meta_keywords' => trim((string) ($validated['meta_keywords'] ?? '')) ?: null,
            'canonical_url' => trim((string) ($validated['canonical_url'] ?? '')) ?: null,
            'og_image' => trim((string) ($validated['og_image'] ?? '')) ?: null,
        ];
    }

    protected function uniqueSlug(string $base, ?int $ignoreVendorId = null): string
    {
        $base = $base !== '' ? $base : 'shop';
        $slug = $base;
        $i = 1;

        while ($this->slugExists($slug, $ignoreVendorId)) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, ?int $ignoreVendorId = null): bool
    {
        return Vendor::query()
            ->when($ignoreVendorId, fn ($q) => $q->where('id', '!=', $ignoreVendorId))
            ->where('slug', $slug)
            ->exists();
    }
}
