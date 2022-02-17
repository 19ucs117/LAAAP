<?php

namespace App\Http\Controllers;

use App\Models\course_code;
use App\Models\Department;
use App\Models\Role;
use App\Models\SyllabusAssign;
use App\Models\SyllabusCourses;
use App\Models\User;
use App\Models\program;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

}
