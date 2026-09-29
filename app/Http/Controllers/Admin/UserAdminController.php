<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function index()
    {
        $users = User::orderByDesc('id')->paginate(30);

        return view('admin.users', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:user,vendor,admin']);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot change your own role here.');
        }
        $user->update(['role' => $request->role]);

        return back()->with('status', 'Role updated.');
    }
}
