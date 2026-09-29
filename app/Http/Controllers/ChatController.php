<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Vendor $vendor)
    {
        $user = auth()->user();
        if ($user->isVendor() && $user->vendor && $user->vendor->id === $vendor->id) {
            $customerId = request('with');
            abort_unless($customerId, 404);
            $messages = ChatMessage::where('user_id', $customerId)
                ->where('vendor_id', $vendor->id)
                ->orderBy('id')
                ->take(300)
                ->get();
            $customer = User::findOrFail($customerId);

            return view('market.chat', [
                'vendor' => $vendor,
                'messages' => $messages,
                'customerUserId' => (int) $customerId,
                'customerName' => $customer->name,
                'isVendorView' => true,
            ]);
        }

        $messages = ChatMessage::where('user_id', $user->id)
            ->where('vendor_id', $vendor->id)
            ->orderBy('id')
            ->take(300)
            ->get();

        return view('market.chat', [
            'vendor' => $vendor,
            'messages' => $messages,
            'customerUserId' => null,
            'customerName' => null,
            'isVendorView' => false,
        ]);
    }

    public function store(Request $request, Vendor $vendor)
    {
        $request->validate(['body' => 'required|string|max:2000']);

        $user = auth()->user();
        if ($user->isVendor() && $user->vendor && $user->vendor->id === $vendor->id) {
            $request->validate(['customer_user_id' => 'required|exists:users,id']);
            ChatMessage::create([
                'user_id' => $request->customer_user_id,
                'vendor_id' => $vendor->id,
                'order_id' => $request->order_id,
                'sender_role' => 'vendor',
                'body' => $request->body,
            ]);

            return redirect()->route('chat.show', [$vendor, 'with' => $request->customer_user_id]);
        }

        ChatMessage::create([
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'order_id' => $request->order_id,
            'sender_role' => 'user',
            'body' => $request->body,
        ]);

        return redirect()->route('chat.show', $vendor);
    }
}
