<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\Assessment_copo;
use App\Models\Assessment_peopso;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

use App\Models\course_code;
use App\Models\Assignstaff;
use App\Models\Department;
use App\Models\Indirectpo;
use App\Models\Indirectpso;
use App\Models\Studentmark;
use App\Models\User;

class AssessmentController extends Controller
{
    //
    public function __construct()
    {
      $this->middleware(['auth', 'hod']);
    }

    public function getAllCoursesHodWithProgramID($program_id, $semesterNo){
        $matching = ['course_codes.program_id' => $program_id, 'course_codes.semester' => $semesterNo];
        // $courses=course_code::select('course_code','course_title','course_title','credits','hours','category','semester')
        //     ->where($matching)
        //     ->get();
        $courses = course_code::join('departments', 'departments.id', '=', 'course_codes.department_id')
            ->join('programs', 'programs.id', '=', 'course_codes.program_id')
            ->select('course_codes.id','programs.id as program_id','programs.department_id','departments.department_name','programs.program_name','course_codes.course_code','course_codes.course_title','course_codes.credits','course_codes.hours','course_codes.category','course_codes.semester')
            ->where($matching)
            ->get();

        return response()->json($courses);
    }

    public function assignStaff(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
          'department_id' => ['required'],
          'program_id' => ['required'],
          'course_id' => ['required'],
          'batchNo' => ['required'],
          'section' => ['required'],
          'user_id' => ['required'],
      ]);
      try {
            Assignstaff::create([
                'id' => Str::uuid(),
                'department_id' => $request->department_id,
                'program_id' => $request->program_id,
                'course_id' => $request->course_id,
                'batch_id' => $request->batchNo,
                'section' => $request->section,
                'user_id' => $request->user_id,
                'assigned_by' => auth()->user()->id,
            ]);
          $status = 'success';
          $message = "Staff Assigned Successfully";
      } catch (Exception $e) {
          Log::warning('Error Assigning Staff');
          $status = 'error';
          $message = $e;
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function getSectionAssignedStaff($departmentId)
    {
      $AssignedStaff = Assignstaff::join('departments', 'departments.id', '=', 'assignstaffs.department_id')
                          ->join('programs', 'programs.id', '=', 'assignstaffs.program_id')
                          ->join('course_codes', 'course_codes.id', '=', 'assignstaffs.course_id')
                          ->join('batch_details', 'batch_details.id', '=', 'assignstaffs.batch_id')
                          ->join('users', 'users.id', '=', 'assignstaffs.user_id')
                          ->select('assignstaffs.id', 'assignstaffs.section',
                                   'batch_details.id as batch_id', 'batch_details.batchNo', 'batch_details.NoSections',
                                   'departments.id as department_id', 'departments.department_name',
                                   'programs.id as program_id', 'programs.program_name',
                                   'course_codes.id as course_id','course_codes.course_title', 'course_codes.course_code', 'course_codes.semester',
                                   'users.name','users.id as user_id', 'users.department_number')
                          ->where('programs.department_id', $departmentId)
                          ->get();
      return response()->json($AssignedStaff);
    }

    public function updateAssignedStaff(Request $request, $assignedStaffID)
    {
      $status = "";
      $message = "";
      $request->validate([
        'user_id' => ['required'],
        'course_id' => ['required'],
        'program_id' => ['required'],
        'department_id' => ['required'],
        'batchNo' => ['required'],
        'section' => ['required'],
      ]);
      try {
        $AssignedStaff = Assignstaff::find($assignedStaffID);
        if (!is_null($AssignedStaff)) {
          $AssignedStaff->department_id = $request->department_id;
          $AssignedStaff->program_id = $request->program_id;
          $AssignedStaff->course_id = $request->course_id;
          $AssignedStaff->batch_id = $request->batchNo;
          $AssignedStaff->section = $request->section;
          $AssignedStaff->user_id = $request->user_id;
          $AssignedStaff->save();
        }
        $status = "success";
        $message = "Assigned Staff Updated Successfully";
      } catch (Exception $e) {
        log::warning('Error Updating Assigned Staff');
        $status = "error";
        $message = "Unable To Update Assigned Staff";
      }
      return response()->json(['status' => $AssignedStaff, 'message' => $message]);
    }

    public function deleteAssignedStaff($assignedStaff_id)
    {
      $status = "";
      $message = "";
      try {
        $assignedStaff = Assignstaff::find($assignedStaff_id);
        if (!is_null($assignedStaff)) {
          $assignedStaff->delete();
        }
        $status = "success";
        $message = "Assigned Staff Deleted Successfully";
      } catch (Exception $e) {
        log::warning('Error Deleting Assigned Staff');
        $status = "error";
        $message = "Unable To Delete Assigned Staff";
      }
      return response()->json(['status' => $assignedStaff, 'message' => $message]);
    }

    public function postPEOPSOmapping(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'regulation' => 'required',
        'program_name' => 'required',
        'psopeos' => 'required'
      ]);
  
      try{
        Assessment_peopso::create([
          'id' => Str::uuid(),
          'regulation' => $request->regulation,
          'program_name' => $request->program_name,
          'direct_attainment' => json_encode($request['psopeos'])
        ]);
        $status = "success";
        $message="Successfully Mapping Uploaded";
      }catch(Exception $e){
        $status = "error";
        $message="Unable to Upload Mapping";
      }
  
      return response()->json(['status'=>$status, 'message'=>$message]);
    }
  
    public function getCOPOmapping($id)
    {
  
      $copo = DB::select('SELECT direct_attainment FROM assessment_copos WHERE (co_id = :courseOUTCOME_id)', ['courseOUTCOME_id' => $id]);
      $copoIndirect = DB::select('SELECT indirect_assessment FROM assessment_copos WHERE (co_id = :courseOUTCOME_id)', ['courseOUTCOME_id' => $id]);
      
      if ($copo == null && $copoIndirect == null) {
        $copo = array();
        $copoIndirect = array();
        return response()->json(['copos' => $copo, 'indirect_assessment'=>$copoIndirect]);
      } else {
        $mapping = $copo[0];
        foreach ($mapping as $key => $value) {
          $Key =  $value;
        }
        $Key = json_decode($Key, true);
      }
      if($copoIndirect == null){
        $copoIndirect = array();
        return response()->json(['copos' => $Key, 'indirect_assessment'=>$copoIndirect]);
      }
      else{
        $indirect = $copoIndirect[0];
        foreach ($indirect as $key => $value) {
          $indirect_copo =  $value;
        }
        $copo_indirect_read = json_decode($indirect_copo, true);
        return response()->json(['copos' => $Key, 'indirect_assessment'=>$copo_indirect_read]);
      }
    }
  
    public function editCOPOmapping(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'co_id' => 'required',
        'pocos' => 'required'
      ]);
  
      try{
        $copo = Assessment_copo::where('co_id', $request->co_id)->get();
        $copo[0]->direct_attainment = json_encode($request->pocos);
        $copo[0]->save();
        $status = "success";
        $message="Successfully Mapping Updated";
      }catch(Exception $e){
        $status = "error";
        $message="Unable to Update Mapping";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }
  
    public function editCOPOdirectAssessment(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'co_id' => 'required',
        'direct_assessment' => 'required'
      ]);
  
      try{
        $copo = Assessment_copo::where('co_id', $request->co_id)->get();
        $copo[0]->copo = json_encode($request->direct_assessment);
        $copo[0]->save();
        $status = "success";
        $message="Successfully DirectAssessment Updated";
      }catch(Exception $e){
        $status = "error";
        $message="Unable to Update DirectAssessment";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }
  
    public function editCOPOIndirectAssessment(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'co_id' => 'required',
        'indirect_assessment_upload' => 'required',
        'indirectAssessment' => 'required'
      ]);
  
      try{
        $copo = Assessment_copo::where('co_id', $request->co_id)->get();
        $copo[0]->indirect_assessment = json_encode($request->indirect_assessment_upload);
        $copo[0]->indirect_attainment = json_encode($request->indirectAssessment);
        $copo[0]->save();
        $status = "success";
        $message="Successfully InDirectAssessment Updated";
      }catch(Exception $e){
        $status = "error";
        $message="Unable to Update InDirectAssessment";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }
  
    public function deleteCOPOIndirectAssessment(Request $request, $co_id)
    {
      $status = "";
      $message = "";
  
      try{
        $copo = Assessment_copo::where('co_id', $co_id)->get();
        $copo[0]->indirect_assessment = null;
        $copo[0]->indirect_attainment = null;
        $copo[0]->save();
        $status = "success";
        $message="Successfully InDirectAssessment Deleted";
      }catch(Exception $e){
        $status = "error";
        $message="Unable to Deleted InDirectAssessment";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }
  
    public function copoReport(Request $request, $co_id)
    {
      $copo = DB::select('SELECT copo FROM assessment_copos WHERE (co_id = :courseOUTCOME_id)', ['courseOUTCOME_id' => $co_id]);
      $copoIndirect = DB::select('SELECT indirect_attainment FROM assessment_copos WHERE (co_id = :courseOUTCOME_id)', ['courseOUTCOME_id' => $co_id]);
      
      if ($copo == null && $copoIndirect == null) {
        $copo = array();
        $copoIndirect = array();
        return response()->json(['direct_assessment' => $copo, 'indirect_assessment'=>$copoIndirect]);
      } else {
        $mapping = $copo[0];
        foreach ($mapping as $key => $value) {
          $Key =  $value;
        }
        $Key = json_decode($Key, true);
      }
      if($copoIndirect == null){
        $copoIndirect = array();
        return response()->json(['direct_assessment' => $Key, 'indirect_assessment'=>$copoIndirect]);
      }
      else{
        $indirect = $copoIndirect[0];
        foreach ($indirect as $key => $value) {
          $indirect_copo =  $value;
        }
        $copo_indirect_read = json_decode($indirect_copo, true);
        return response()->json(['direct_assessment' => $Key, 'indirect_assessment'=>$copo_indirect_read]);
      }
    }

    public function createPSOindirectAssessment(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'regulation' => 'required',
        'department_name' => 'required',
        'program_name' => 'required',
        'indirect_assessment_mark' => 'required',
        'indirect_assessment_mark_100' => 'required',
        'indirect_assessment_mark_10' => 'required'
      ]);
  
      try{
        $pso = Indirectpso::create([
          'id' => Str::uuid(),
          'regulation' => $request->regulation,
          'department_name' => $request->department_name,
          'program_name' => $request->program_name,
          'staff_id' => $request->staff_id,
          'indirect_assessment' => json_encode($request->indirect_assessment_mark),
          'indirect_assessment_100_percentage' => json_encode($request->indirect_assessment_mark_100),
          'indirect_assessment_10_percentage' => json_encode($request->indirect_assessment_mark_10),
        ]);
        $status = "success";
        $message="Successfully created InDirectAssessment";
      }catch(Exception $e){
        $status = "error";
        $message="Unable to created InDirectAssessment";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function getPSOindirectAssessment()
    {
      $status = "";
      $message = "";
      try{
        $departmentName = User::join('departments', 'departments.id', '=', 'users.department_id')
                                ->select('departments.department_name')
                                ->where('departments.id', auth()->user()->department_id)
                                ->limit(1)
                                ->get();
        $indirectPSO = Indirectpso::where('department_name', $departmentName[0]["department_name"])
                                    ->select('id', 'program_name', 'regulation')
                                    ->get();
        return response()->json($indirectPSO);
      }catch(Exception $e){
        $status = "error";
        $message="Unable to Retrieve InDirectAssessment";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function editPSOindirectAssessment(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'id' => 'required',
        'regulation' => 'required',
        'department_name' => 'required',
        'program_name' => 'required',
        'indirect_assessment_mark' => 'required',
        'indirect_assessment_mark_100' => 'required',
        'indirect_assessment_mark_10' => 'required'
      ]);
  
      try{
        $pso = Indirectpso::find(['id', $request->id]);
        $pso->regulation = $request->regulation;
        $pso->department_name = $request->department_name;
        $pso->program_name = $request->program_name;
        $pso->staff_id = $request->staff_id;
        $pso->indirect_assessment = $request->indirect_assessment_mark;
        $pso->indirect_assessment_100_percentage = $request->indirect_assessment_mark_100;
        $pso->indirect_assessment_10_percentage = $request->indirect_assessment_mark_10;
        $pso->save();
        $status = "success";
        $message="Successfully InDirectAssessment Updated";
      }catch(Exception $e){
        $status = "error";
        $message="Unable to Update InDirectAssessment";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function deletePSOindirectAssessment(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'id' => 'required'
      ]);
  
      try{
        $pso = Indirectpso::find($request->id);
        $pso->delete();
        $status = "success";
        $message="Successfully Deleted InDirectAssessment";
      }catch(Exception $e){
        $status = "error";
        $message="Unable to delete InDirectAssessment";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function createPOindirectAssessment(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'regulation' => 'required',
        'school_name' => 'required',
        'indirect_assessment_mark' => 'required',
        'indirect_assessment_mark_100' => 'required',
        'indirect_assessment_mark_10' => 'required'
      ]);
  
      try{
        $pso = Indirectpo::create([
          'id' => Str::uuid(),
          'regulation' => $request->regulation,
          'school_name' => $request->school_name,
          'staff_id' => $request->staff_id,
          'indirect_assessment' => json_encode($request->indirect_assessment_mark),
          'indirect_assessment_100_percentage' => json_encode($request->indirect_assessment_mark_100),
          'indirect_assessment_10_percentage' => json_encode($request->indirect_assessment_mark_10),
        ]);
        $status = "success";
        $message="Successfully created InDirectAssessment";
      }catch(Exception $e){
        $status = "error";
        $message="Unable to created InDirectAssessment";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function deletePOindirectAssessment(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'id' => 'required'
      ]);
  
      try{
        $pso = Indirectpso::find($request->id);
        $pso->delete();
        $status = "success";
        $message="Successfully Deleted InDirectAssessment";
      }catch(Exception $e){
        $status = "error";
        $message="Unable to delete InDirectAssessment";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function getPOindirectAssessment()
    {
      $status = "";
      $message = "";
      try{

        $indirectPO = Indirectpo::select('id', 'school_name', 'regulation')
                                    ->get();
        return response()->json($indirectPO);
      }catch(Exception $e){
        $status = "error";
        $message="Unable to Retrieve InDirectAssessment";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }
}
