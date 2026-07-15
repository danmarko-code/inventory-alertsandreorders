<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AccountSwitcherController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        session(['acting_admin_id' => $request->user_id]);

        return back()->with('success', 'Now acting as ' . User::find($request->user_id)->name . '.');
    }
}
