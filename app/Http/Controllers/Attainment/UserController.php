<?php

namespace App\Http\Controllers\Attainment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

use App\Models\school;
use App\Models\program;
use App\Models\Department;
use App\Models\Studentmark;
use App\Models\Assessment_copso;
use App\Models\Assessment_copo;
use App\Models\Assessment_peopso;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function Report($school_id)
    {
        $rawDATA = Department::join('programs', 'programs.department_id', '=', 'departments.id')
                        ->join('schools', 'schools.id', '=', 'departments.school_id')
                        ->select('schools.school_name', 'departments.department_name', 'programs.program_name')
                        ->where('schools.id', $school_id)->get();
        $data = array();
        foreach ($rawDATA as $value => $key){
            $matching = ["department_name" => $key['department_name'], "program_name" => $key['program_name']];
            $query = Studentmark::where($matching)->select('id')->get();
            $psopos = Assessment_copso::where('co_id', $query[$value]['id']);
            return response()->json($psopos);
            // $mapping = $query;
            // foreach ($mapping as $key => $value) {
            // $Key =  ($value);
            // }
            // if($Key != null){
            //     array_push($data, json_decode($Key['co']));
            // }
        }
        return response()->json($data);
    }

    public function assessment_psopeos(Request $request)
    {
        $status = "";
        $message = "";
        $request->validate([
            'program_name' => 'required',
            'psopeos' => 'required'
        ]);
        try {
            $matching = ["programs.department_id" => auth()->user()->department_id, "programs.program_name" => $request->program_name];
            $data = program::join('departments', 'departments.id', '=', 'programs.department_id')
                    ->select('programs.school_id', 'departments.id as department_id', 'departments.department_name', 'programs.id as program_id', 'programs.program_name')
                    ->where($matching)
                    ->get();
            Assessment_peopso::CREATE([
                'id' => Str::uuid(),
                'academic_year' => date('M-Y'),
                'school_id' => $data[0]['school_id'],
                'department_id' => $data[0]['department_id'],
                'program_id' => $data[0]['program_id'],
                'direct_attainment' => json_encode($request->psopeos)
            ]);

            $status = "success";
            $message = "PEO-PSO Mapping Successfully Stored In DB";
        } catch (Exception $e) {
            Log::warning('Error Storing PEO-PSO Mapping',$e->getMessage());
            $status = "error";
            $message = "Unable To Store PEO-PSO Mapping";
        }
        return response()->json(["status"=>$status, "message"=>$message]);
    }

    public function getPeoPsosMapping($program_name)
    {
        $matching = ["department_id" => auth()->user()->department_id, "program_name" => $program_name];
        $program_data = program::where($matching)->get();
        array_pop($matching);
        $data = array();
        $matching["program_id"] = $program_data[0]['id'];
        $PeoPsos = Assessment_peopso::where($matching)->select('direct_attainment', 'id')->get();
        try{
            $data['peopsos'] = json_decode($PeoPsos[0]['direct_attainment']);
            $data['id'] = $PeoPsos[0]['id'];
        }catch(Exception $e){
            return response()->json(["peopsos" => array()]);
        }
        return response()->json($data);
    }

    public function updatePsoPeosMapping(Request $request, $mapping_id)
    {
        $status = "";
        $messgae = "";
        try{
            $PeoPsos = Assessment_peopso::find($mapping_id);
            if($PeoPsos != null){
                $PeoPsos -> direct_attainment = json_encode($request->psopeos);
                $PeoPsos -> save();
            }
            $status = "success";
            $message = "Successfully Update PEO-PSO Mapping";
        }catch(Exception $e){
            Log::warning('Error Updating PEO-PSO Mapping',$e->getMessage());
            $status = "error";
            $message = "Unable To Update PEO-PSO Mapping";
        }
        return response()->json(["status"=>$status, "message"=>$message]);
    }
}
