<?php

namespace App\Http\Middleware;

use App\Enums\UserRoles;
use App\Models\Store;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientAuth
{
    /**
     * Handle client role requests.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle (Request $request, Closure $next)
    {

        $user = User::find(Auth::id());

        switch ($user->role) {
            case UserRoles::STORE:
            {
                $request->merge(['model_type' => new Store(), 'model_id' => $user->store->id]);
                return $next($request);
            }
            case UserRoles::USER:
            {
                $request->merge(['model_type' => new User(), 'model_id' => Auth::id()]);
                return $next($request);
            }
            default:
                return response()->json('User does not have the right permissions', 403);
        }
    }
}
