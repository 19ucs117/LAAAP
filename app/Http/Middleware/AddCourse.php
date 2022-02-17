<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class AddCourse
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

      if (($user->role_name == 'HOD') OR ($user->role_name == 'Super Admin')) {
          return $next($request);
      } else {
          auth()->logout();
      }
    }
}
