<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\vissionMission;
use App\Models\school;
use App\Models\Department;
use App\Models\program;
use App\Models\Peo;
use App\Models\Po;
use App\Models\Pso;
use App\Models\missionvisionpeo;
use App\Models\Peopso;
use App\Models\Psopo;
use App\Models\Peopo;
class MappingController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function getVissionMission(Request $request)
    {
      $vissionMission = vissionMission::select('id','vision','mission_one','mission_two','mission_three', 'mission_four')->get();
      return response()->json($vissionMission);
    }

    public function getPeo(Request $request, $schoolId)
    {
      $peo = Peo::join('schools', 'schools.id', '=', 'peos.school_id')
          ->select('schools.school_name','peos.id','peos.labelNo','peos.description')
          ->where('peos.school_id', $schoolId)
          ->orderBy('peos.labelNo', 'asc')
          ->get();
      $VisionAndMissionPeoMapping = missionvisionpeo::select('mapping')->where('school_id', $schoolId);
      $peoCount = Peo::where('school_id', $request->school_id)->count();
      return response()->json(['peo'=>$peo, 'peoCount'=>$peoCount, 'vmpeoMappping'=>$VisionAndMissionPeoMapping]);
    }

    public function getPo(Request $request, $schoolId)
    {
      $po = Po::join('schools', 'schools.id', '=', 'pos.school_id')
          ->select('schools.school_name','pos.id','pos.labelNo','pos.description')
          ->where('pos.school_id', $schoolId)
          ->orderBy('pos.labelNo', 'asc')
          ->get();
      $peopo = Peopo::select('mapping')->where('school_id', $schoolId);
      $poCount = Po::where('school_id', $request->school_id)->count();
      return response()->json(['po'=>$po, 'poCount'=>$poCount, 'peopoMapping'=>$peopo]);
    }

    public function getPso(Request $request, $programId)
    {
      $pso = Pso::join('programs', 'programs.id', '=', 'psos.program_id')
          ->join('departments', 'departments.id', '=', 'psos.department_id')
          ->select('departments.department_name','programs.program_name','psos.id','psos.labelNo','psos.description','psos.department_id','psos.program_id')
          ->where('psos.program_id', $programId)
          ->orderBy('psos.labelNo', 'asc')
          ->get();
      $psopeoMapping = Peopso::select('mapping')->where('program_id', $programId);
      $psopoMapping = Psopo::select('mapping')->where('program_id', $programId);
      $psoCount = Pso::where('program_id', $programId)->count();
      return response()->json(['pso'=>$pso, 'psoCount'=>$psoCount,'psopo'=>$psopoMapping, 'psopeo'=>$psopeoMapping]);
    }

    public function getDepartmentAndProgram(Request $request, $departmentId)
    {
      // $program = program::select('school_id', 'id', 'department_id')->get();
      $program = program::join('departments', 'departments.id', '=', 'programs.department_id')
          ->join('schools', 'schools.id', '=', 'programs.school_id')
          ->select('schools.school_name','departments.department_name','programs.id','programs.program_name')
          ->where('programs.department_id', $departmentId)
          ->get();
      return response()->json($program);
    }

}
