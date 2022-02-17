<?php

namespace App\Http\Middleware;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class staff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
      $user = User::join('roles', 'roles.id', '=', 'users.role_id')->select('users.name', 'roles.role_name')->where('users.id', auth()->user()->id)->first();

      if (($user->role_name == 'Staff') OR ($user->role_name == 'HOD')) {
          return $next($request);
      } else {
          auth()->logout();
      }
    }
}
