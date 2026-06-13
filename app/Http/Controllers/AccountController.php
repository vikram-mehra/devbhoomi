<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\ReturnModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $user = auth()->user()->load(['addresses' => fn ($q) => $q->orderByDesc('is_default')]);

        $ordersCount = $user->orders()->visibleInAccount()->count();

        $defaultAddress = $user->addresses->firstWhere('is_default') ?? $user->addresses->first();
        $addressLine = $defaultAddress
            ? trim(implode(', ', array_filter([
                $defaultAddress->line1,
                $defaultAddress->line2,
                $defaultAddress->city,
                $defaultAddress->state,
                $defaultAddress->pincode,
            ])))
            : null;

        return view('market.account.dashboard', compact(
            'user',
            'ordersCount',
            'defaultAddress',
            'addressLine'
        ));
    }

    public function details()
    {
        return view('market.account.account-details', ['user' => auth()->user()]);
    }

    public function updateDetails(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:32', Rule::unique('users')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => (($data['phone'] ?? '') === '') ? null : $data['phone'],
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar && ! Str::startsWith($user->avatar, ['http://', 'https://'])) {
                Storage::disk('public')->delete($user->avatar);
            }
            $payload['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($payload);

        return redirect()->route('account.details')->with('status', __('Account details updated.'));
    }

    public function refunds()
    {
        $returns = ReturnModel::query()
            ->where('user_id', auth()->id())
            ->with('order')
            ->latest()
            ->paginate(15);

        return view('market.account.refunds', compact('returns'));
    }

    public function addresses()
    {
        $addresses = auth()->user()->addresses()->orderByDesc('is_default')->get();

        return view('market.account.addresses', compact('addresses'));
    }

    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'label' => 'nullable|string|max:64',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:32',
            'line1' => 'required|string|max:255',
            'line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:120',
            'state' => 'required|string|max:120',
            'pincode' => 'required|string|max:16',
        ]);

        $user = $request->user();
        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create([
            'label' => $data['label'] ?? null,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'line1' => $data['line1'],
            'line2' => $data['line2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'pincode' => $data['pincode'],
            'is_default' => $isDefault,
        ]);

        return redirect()->route('account.addresses.index')->with('status', __('Address saved.'));
    }

    public function editAddress(Address $address)
    {
        abort_unless($address->user_id === auth()->id(), 403);

        return view('market.account.address-edit', compact('address'));
    }

    public function updateAddress(Request $request, Address $address)
    {
        abort_unless($address->user_id === auth()->id(), 403);

        $data = $request->validate([
            'label' => 'nullable|string|max:64',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:32',
            'line1' => 'required|string|max:255',
            'line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:120',
            'state' => 'required|string|max:120',
            'pincode' => 'required|string|max:16',
        ]);

        $user = $request->user();
        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update([
            'label' => $data['label'] ?? null,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'line1' => $data['line1'],
            'line2' => $data['line2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'pincode' => $data['pincode'],
            'is_default' => $isDefault,
        ]);

        return redirect()->route('account.addresses.index')->with('status', __('Address updated.'));
    }

    public function setDefaultAddress(Address $address)
    {
        abort_unless($address->user_id === auth()->id(), 403);

        auth()->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('status', __('Default address updated.'));
    }

    public function destroyAddress(Address $address)
    {
        abort_unless($address->user_id === auth()->id(), 403);
        $address->delete();

        return redirect()->route('account.addresses.index')->with('status', __('Address removed.'));
    }

    public function passwordForm()
    {
        return redirect()->to(route('account.details').'#account-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->to(route('account.details').'#account-password')->with('status', __('Password updated.'));
    }
}
