<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        if ($request->get('type') === 'admin') {
            return $this->admins($request);
        }

        return redirect()->route('admin.users.customers');
    }

    public function admins(Request $request)
    {
        $type = 'admin';
        $q = User::query()->where('role', '!=', 'user');

        if ($request->filled('q')) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->get('q')).'%';
            $q->where(function ($qq) use ($term) {
                $qq->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('role', 'like', $term);
            });
        }

        $users = $q->orderByDesc('id')->paginate(30)->withQueryString();

        return view('admin.users', compact('users', 'type'));
    }

    public function customers(Request $request)
    {
        $type = 'customer';
        $q = User::query()->where('role', 'user')->withCount('orders');

        if ($request->filled('q')) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->get('q')).'%';
            $q->where(function ($qq) use ($term) {
                $qq->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        $users = $q->orderByDesc('id')->paginate(30)->withQueryString();

        return view('admin.users', compact('users', 'type'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:user,vendor,admin,seo']);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot change your own role here.');
        }
        $user->update(['role' => $request->role]);

        return back()->with('status', 'Role updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', __('You cannot delete your own account.'));
        }

        DB::transaction(function () use ($user) {
            // 1. If user is vendor, delete products and vendor profile
            if ($user->vendor) {
                $vendor = $user->vendor;
                foreach ($vendor->products as $product) {
                    $product->load('images', 'variants');
                    foreach ($product->images as $im) {
                        if ($im->path && !Str::startsWith($im->path, ['http://', 'https://'])) {
                            Storage::disk('public')->delete($im->path);
                        }
                    }
                    foreach ($product->variants as $v) {
                        if ($v->image && !Str::startsWith($v->image, ['http://', 'https://'])) {
                            Storage::disk('public')->delete($v->image);
                        }
                    }
                    $product->delete();
                }
                $vendor->delete();
            }

            // 2. Orders and all order-related data
            $orderIds = DB::table('orders')->where('user_id', $user->id)->pluck('id');
            if ($orderIds->isNotEmpty()) {
                // Break circular FK between orders and shipping_addresses
                DB::table('orders')->whereIn('id', $orderIds)->update(['shipping_address_id' => null]);

                // Shipments and their tracking events
                $shipmentIds = DB::table('shipments')->whereIn('order_id', $orderIds)->pluck('id');
                if ($shipmentIds->isNotEmpty()) {
                    DB::table('shipment_tracking_events')->whereIn('shipment_id', $shipmentIds)->delete();
                    DB::table('shipments')->whereIn('id', $shipmentIds)->delete();
                }

                DB::table('order_tracking_events')->whereIn('order_id', $orderIds)->delete();
                DB::table('shipping_addresses')->whereIn('order_id', $orderIds)->delete();
                DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
                DB::table('order_activity_logs')->whereIn('order_id', $orderIds)->delete();
                DB::table('coupon_usages')->whereIn('order_id', $orderIds)->delete();
                DB::table('returns')->whereIn('order_id', $orderIds)->delete();
                DB::table('orders')->whereIn('id', $orderIds)->delete();
            }

            // 3. User direct relations
            DB::table('order_activity_logs')->where('user_id', $user->id)->delete();
            DB::table('coupon_usages')->where('user_id', $user->id)->delete();
            DB::table('returns')->where('user_id', $user->id)->delete();
            DB::table('reviews')->where('user_id', $user->id)->delete();
            DB::table('cart_items')->where('user_id', $user->id)->delete();
            DB::table('wishlists')->where('user_id', $user->id)->delete();
            DB::table('addresses')->where('user_id', $user->id)->delete();
            DB::table('wallet_transactions')->where('user_id', $user->id)->delete();
            DB::table('chat_messages')->where('user_id', $user->id)->delete();
            DB::table('email_verification_otps')->where('user_id', $user->id)->delete();
            DB::table('password_resets')->where('email', $user->email)->delete();

            // Nullify audit logs where user was admin
            DB::table('inventory_logs')->where('admin_user_id', $user->id)->update(['admin_user_id' => null]);
            DB::table('purchases')->where('admin_user_id', $user->id)->update(['admin_user_id' => null]);
            DB::table('sales')->where('admin_user_id', $user->id)->update(['admin_user_id' => null]);
            DB::table('stock_movements')->where('admin_user_id', $user->id)->update(['admin_user_id' => null]);
            DB::table('warehouse_transfers')->where('admin_user_id', $user->id)->update(['admin_user_id' => null]);

            // Tokens
            $user->tokens()->delete();

            // Avatar file
            if ($user->avatar && !Str::startsWith($user->avatar, ['http://', 'https://'])) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Delete user
            $user->delete();
        });

        return back()->with('status', __('User and all associated data have been permanently deleted.'));
    }
}
