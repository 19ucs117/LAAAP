<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;



use App\Models\Peopo;
use App\Models\Missionvisionpo;
use App\Models\missionvisionpeo;

use App\Models\school;
use App\Models\vissionMission;
use App\Models\Peo;
use App\Models\Po;
use App\Models\Pso;
use App\Models\program;

class MappingProgress extends Controller
{

    //
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function progressInVisionMissionPeoMapping()
    {
      $isCompleted = 0;
      $isCompletedVisionMission = vissionMission::all()->count();
      $nocompletedPeos = Peo::all()->unique('school_id')->count();
      $noOfschools = school::all()->count();
      if ($nocompletedPeos == $noOfschools) {
        if ($isCompletedVisionMission != 0) {
          $isCompleted = 1;
        }
      }
      return response()->json($isCompleted);
    }

    public function progressInPeoPoMapping()
    {
      $isCompleted = 0;
      $nocompletedPeos = Peo::all()->unique('school_id')->count();
      $nocompletedPos = Po::all()->unique('school_id')->count();
      $noOfschools = school::all()->count();
      if ($nocompletedPeos == $nocompletedPos) {
        if ($noOfschools == $nocompletedPos) {
          $isCompleted = 1;
        }
      }
      return response()->json($isCompleted);
    }

    public function progressInPsoPoMapping($departmentId)
    {
      $isCompleted = 0;
      $nocompletedPsos = Pso::where('department_id', $departmentId)->distinct('program_id')->count();
      $nocompletedPos = Po::all()->unique('school_id')->count();
      $noOfschools = school::all()->count();
      $noOfPrograms = program::all()->unique($departmentId)->count();
      if ($noOfschools == $nocompletedPos) {
        if ($nocompletedPsos == $noOfPrograms) {
          $isCompleted = 1;
        }
      }
      return response()->json($isCompleted);
    }

    public function progressInPsoPeoMapping($departmentId)
    {
      $isCompleted = 0;
      $nocompletedPsos = Pso::where('department_id', $departmentId)->distinct('program_id')->count();
      $nocompletedPeos = Peo::all()->unique('school_id')->count();
      $noOfschools = school::all()->count();
      $noOfPrograms = program::all()->unique($departmentId)->count();
      if ($noOfschools == $nocompletedPeos) {
        if ($nocompletedPsos == $noOfPrograms) {
          $isCompleted = 1;
        }
      }
      return response()->json($isCompleted);
    }

    // public function progressInCoPsoMapping($courseId)
    // {
    //
    // }
    //
    // public function progressInCoPoMapping($courseId)
    // {
    //
    // }
    //
    // public function progressInCoPeoMapping($courseId)
    // {
    //
    // }
    
}
