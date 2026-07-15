<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ResolveActingAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $adminId = session('acting_admin_id');

        $admin = $adminId
            ? User::find($adminId)
            : User::where('role', 'admin')->orderBy('id')->first();

        if ($admin && !session('acting_admin_id')) {
            session(['acting_admin_id' => $admin->id]);
        }

        if ($admin) {
            auth()->setUser($admin);
        }

        View::share('actingAdmin', $admin);
        View::share('allAdmins', User::where('role', 'admin')->orderBy('id')->get());

        return $next($request);
    }
}
