<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Exception;

use App\Models\AssignStaff;
use App\Models\selected_subject;
use App\Models\Exammark;
use App\Models\Studentmark;
use App\Models\Assessment_copso;
use App\Models\Assessment_copo;
use App\Models\Department;

class AssessmentController extends Controller
{
  //
  public function __construct()
  {
    $this->middleware(['auth', 'staff']);
  }

  public function getCoursesWithStaffId($staff_id)
  {
    $assignedCourses = AssignStaff::join('course_codes', 'course_codes.id', '=', 'assignstaffs.course_id')
      ->join('batch_details', 'batch_details.id', '=', 'assignstaffs.batch_id')
      ->select(
        'course_codes.id  as course_id',
        'course_codes.course_code',
        'course_codes.course_title',
        'assignstaffs.id',
        'assignstaffs.batch_id',
        'assignstaffs.section',
        'assignstaffs.department_id',
        'assignstaffs.program_id',
        'batch_details.id as batch_id',
        'batch_details.batchNo'
      )
      ->where('assignstaffs.user_id', $staff_id)
      ->get();
    return response()->json($assignedCourses);
  }

  public function getStudentsWithStaffIdAndSection($staff_id, $section, $course_id, $batchNo)
  {
    $matching = ['assignstaffs.user_id' => $staff_id, 'assignstaffs.course_id' => $course_id, 'student_details.section' => $section, 'selected_subjects.course_id' => $course_id, 'student_details.batch_id' => $batchNo, 'assignstaffs.batch_id' => $batchNo];
    $studentDetails = selected_subject::join('course_codes', 'course_codes.id', '=', 'selected_subjects.course_id')
      ->join('student_details', 'student_details.id', '=', 'selected_subjects.student_id')
      ->join('assignstaffs', 'assignstaffs.department_id', '=', 'course_codes.department_id')
      ->select(
        'course_codes.id  as course_id',
        'course_codes.course_code',
        'course_codes.course_title',
        'student_details.name',
        'student_details.departmentNumber',
        'selected_subjects.id',
        'selected_subjects.student_id',
        'assignstaffs.user_id'
      )
      ->where($matching)
      ->orderBy('student_details.departmentNumber')
      ->get();
    // dd($studentDetails);
    return response()->json($studentDetails);
  }

  public function addMarks(Request $request)
  {
    $status = "";
    $message = "";
    $request->validate([
      'course_id' => ['required'],
      'section' => ['required'],
      'markEntry' => ['required'],
    ]);
    try {
      Exammark::create([
        'id' => Str::uuid(),
        'department_id' => auth()->user()->department_id,
        'batch_id' => $request->batch_id,
        'course_id' => $request->course_id,
        'section' => $request->section,
        'assessment' => json_encode($request->markEntry),
        'saved_by' => auth()->user()->id
      ]);

      $status = "success";
      $message = "Exam Marks Successfully Added";
    } catch (Exception $e) {
      log::warning('Error Adding Marks');
      $status = $e;
      $message = "Unable to Add Marks";
    }
    return response()->json(['status' => $status, 'message' => $message]);
  }

  public function getMarks($section, $course_id)
  {
    $matching = ['section' => $section, 'course_id' => $course_id];
    $examMarkDetails = Exammark::select('id', 'department_id', 'course_id', 'section')
      ->where($matching)
      ->get();
    $KeyValues = array();
    $Key = array();
    $examMark = DB::select('SELECT assessment FROM exammarks WHERE (course_id = :courseid) AND (section = :sectionName)', ['sectionName' => $section, 'courseid' => $course_id]);
    if ($examMark == null) {
      $examMark = array();
      return response()->json(['assessment' => $examMark, 'subDetails' => $examMarkDetails]);
    }
    $mapping = $examMark[0];

    foreach ($mapping as $key => $value) {
      $Key =  $value;
    }
    $Key = json_decode($Key, true);
    for ($i = 0; $i < sizeof($Key); $i++) {
      $KeyValues[$i] = $Key[$i];
    }
    return response()->json(['assessment' => $KeyValues, 'subDetails' => $examMarkDetails]);
  }


  public function updateMarks(Request $request, $examMarkID)
  {
    $status = "";
    $message = "";
    $request->validate([
      'markEntry' => 'required',
    ]);
    try {
      if ($examMarks = Exammark::find($examMarkID)) {
        $examMarks->assessment = $request->markEntry;
        $examMarks->save();
      }

      $status = 'success';
      $message = "Marks Updated Successfully";
    } catch (Exception $e) {
      Log::warning('Error Updating Marks', $e->getMessage());
      $status = 'error';
      $message = "Unable to Update Marks";
    }

    return response()->json(['status' => $status, 'message' => $message]);
  }

  public function addStudentMarks(Request $request)
  {
    $status = "";
    $message = "";

    $request->validate([
      'regulation' => ['required'],
      'course_code' => ['required'],
      'staff_code' => ['required'],
      'section' => ['required'],
      'staff_name' => ['required'],
      'student' => ['required'],
      'coDirectMarks' => ['required'],
      'department_name' => ['required'],
      'program_name' => ['required'],
      'course_title' => ['required']
    ]);

    try {
      Studentmark::create([
        'id' => Str::uuid(),
        'department_name' => $request->department_name,
        'program_name' => $request->program_name,
        'regulation' => $request->regulation,
        'course_code' => $request->course_code,
        'course_title' => $request->course_title,
        'staff_code' => $request->staff_code,
        'section' => $request->section,
        'staff_name' => $request->staff_name,
        'co' => json_encode($request->coDirectMarks),
        'direct_attainment' => json_encode($request->student),
      ]);
      $status = 'success';
      $message = 'Students Marks Added Successfully';
    } catch (Exception $e) {
      Log::warning('Error Adding Marks', $e->getMessage());
      $status = 'error';
      $message = "Unable to Add Marks";
    }
    return response()->json(['status' => $status, 'message' => $message]);
  }


  public function getAcademicYear()
  {
    $data = Studentmark::where('staff_code', auth()->user()->department_number)
      ->select('id', 'course_code', 'regulation', 'course_title', 'section')->get();
    $courses = $data->unique('course_code');
    $academicYear = $data->unique('regulation');
    $section = $data->unique('section');

    return response()->json(['courses' => $courses, 'years' => $academicYear, 'section' => $section, 'data'=>$data]);
  }

  public function uploadFileFEEDBACk()
  {
    $Result = Studentmark::where('staff_code', auth()->user()->department_number)->get();
    return ($Result);
  }

  public function getStudentMarks($academicYear, $coursecode, $section)
  {

    // $matching = ["regulation"=> $academicYear, "staff_code"=>$empcode, "course_code"=>$coursecode, "section"=>$section];
    $examMark = DB::select('SELECT direct_attainment FROM studentmarks WHERE (regulation = :academicYear) AND (staff_code = :empCode) AND (course_code = :courseID) AND (section = :section)', ['academicYear' => $academicYear, 'empCode' => auth()->user()->department_number, 'courseID' => $coursecode, 'section' => $section]);

    if ($examMark == null) {
      $examMark = array();
      return response()->json(['direct_attainment' => $examMark]);
    } else {
      $mapping = $examMark[0];
      foreach ($mapping as $key => $value) {
        $Key =  $value;
      }
      $Key = json_decode($Key, true);
      for ($i = 0; $i < sizeof($Key); $i++) {
        $KeyValues[$i] = $Key[$i];
      }
      $indirectMark = DB::select('SELECT indirect_attainment FROM studentmarks WHERE (regulation = :academicYear) AND (staff_code = :empCode) AND (course_code = :courseID) AND (section = :section)', ['academicYear' => $academicYear, 'empCode' => auth()->user()->department_number, 'courseID' => $coursecode, 'section' => $section]);

      $indirectMarkmapping = $indirectMark[0];

      foreach ($indirectMarkmapping as $key => $value) {
        $indirectMarkmappingKey =  $value;
      }
      if ($indirectMarkmappingKey == null) {
        $indirectMarkmappingKey = array();
        $empty_feedBack = array();
        return response()->json(["direct_assessment" => $KeyValues, "indirect_assessment" => $indirectMarkmappingKey, "feedback" => $empty_feedBack]);
      } else {
        $indirectMarkmappingKey = json_decode($indirectMarkmappingKey, true);
        $feedBack = DB::select('SELECT feed_back FROM studentmarks WHERE (regulation = :academicYear) AND (staff_code = :empCode) AND (course_code = :courseID) AND (section = :section)', ['academicYear' => $academicYear, 'empCode' => auth()->user()->department_number, 'courseID' => $coursecode, 'section' => $section]);

        $indirectMarkFeedBack = $feedBack[0];

        foreach ($indirectMarkFeedBack as $key => $value) {
          $FeedBackJsonKey =  $value;
        }
        if ($FeedBackJsonKey == null) {
          $FeedBackJsonKey = array();
          return response()->json(["direct_assessment" => $KeyValues, "indirect_assessment" => $indirectMarkmappingKey, "feedback" => $FeedBackJsonKey]);
        }
        $FEEDBACK = json_decode($FeedBackJsonKey, true);
      }
    }
    return response()->json(["direct_assessment" => $KeyValues, "indirect_assessment" => $indirectMarkmappingKey, "feedback" => $FEEDBACK]);
  }

  public function getCoDirectAssessmentMarks($academicYear, $coursecode, $section)
  {

    // $matching = ["regulation"=> $academicYear, "staff_code"=>$empcode, "course_code"=>$coursecode, "section"=>$section];
    $examMark = DB::select('SELECT co FROM studentmarks WHERE (regulation = :academicYear) AND (staff_code = :empCode) AND (course_code = :courseID) AND (section = :section)', ['academicYear' => $academicYear, 'empCode' => auth()->user()->department_number, 'courseID' => $coursecode, 'section' => $section]);
    $mapping = $examMark[0];
      foreach ($mapping as $key => $value) {
        $Key =  $value;
      }
      $Key = json_decode($Key, true);
    
    return response()->json($Key);
  }

  public function getCoPsoAssessmentMarks($academicYear, $empcode, $coursecode, $section)
  {

    // $matching = ["regulation"=> $academicYear, "staff_code"=>$empcode, "course_code"=>$coursecode, "section"=>$section];
    $examMark = DB::select('SELECT co FROM studentmarks WHERE (regulation = :academicYear) AND (staff_code = :empCode) AND (course_code = :courseID) AND (section = :section)', ['academicYear' => $academicYear, 'empCode' => $empcode, 'courseID' => $coursecode, 'section' => $section]);
    $mapping = $examMark[0];
      foreach ($mapping as $key => $value) {
        $Key =  $value;
      }
      $Key = json_decode($Key, true);
    
    return response()->json($Key);
  }
  
  public function updateIndirectMarks(Request $request)
  {
    $status = "";
    $message = "";

    $request->validate([
      'indirect_student' => ['required'],
      'course_code' => ['required'],
      'section' => ['required'],
      'staff_code' => ['required'],
      'consolidatedCO'=> ['required'],
      'co_avarage' => ['required']
    ]);

    try {
      $matching = ['course_code' => $request->course_code, 'section' => $request->section, 'staff_code' => $request->staff_code];
      $data = Studentmark::where($matching)->get();
      if (!is_null($data[0])) {
        $data[0]->indirect_attainment = json_encode($request->indirect_student);
        $data[0]->consolidated_co = json_encode($request->consolidatedCO);
        $data[0]->co_avarage = $request->co_avarage;
        $data[0]->save();
      } else {
        return (0);
      }
      $status = 'success';
      $message = 'Indirect Marks Updated Successfully';
    } catch (Exception $e) {
      Log::warning('Error Updating Marks', $e->getMessage());
      $status = 'error';
      $message = "Unable to Update Marks";
    }
    return response()->json(['status' => $status, 'message' => $message]);
  }

  public function deleteIndirectMarks($id)
  {
    $status = "";
    $message = "";

    try {
      $data = Studentmark::find($id);
      if (!is_null($data)) {
        $data->indirect_attainment = null;
        $data->save();
      } else {
        return (0);
      }
      $status = 'success';
      $message = 'Indirect Marks Updated Successfully';
    } catch (Exception $e) {
      Log::warning('Error Updating Marks', $e->getMessage());
      $status = 'error';
      $message = "Unable to Update Marks";
    }
    return response()->json(['status' => $status, 'message' => $message]);
  }

  public function updateIndirectMarksFeedback(Request $request, $id)
  {
    $status = "";
    $message = "";

    $request->validate([
      'indirectMarksFeedBack' => ['required'],
    ]);

    try {
      $data = Studentmark::find($id);
      if (!is_null($data)) {
        $data->feed_back = json_encode($request->indirectMarksFeedBack);
        $data->save();
      } else {
        return (0);
      }
      $status = 'success';
      $message = 'Indirect Marks FeedBack Updated Successfully';
    } catch (Exception $e) {
      Log::warning('Error Updating FeedBack', $e->getMessage());
      $status = 'error';
      $message = "Unable to Update FeedBack";
    }
    return response()->json(['status' => $status, 'message' => $message]);
  }

  public function updateStudentMarks(Request $request, $id)
  {
    $status = "";
    $message = "";

    $request->validate([
      'regulation' => ['required'],
      'course_code' => ['required'],
      'staff_code' => ['required'],
      'section' => ['required'],
      'staff_name' => ['required'],
      'student' => ['required']
    ]);

    try {
      if ($data = Studentmark::find($id)) {
        $data->regulation = $request->regulation;
        $data->course_code = $request->course_code;
        $data->staff_code = $request->staff_code;
        $data->section = $request->section;
        $data->staff_name = $request->staff_name;
        $data->assessment = json_encode($request->student);
      }
      $status = 'success';
      $message = 'Student Marks Updated Successfully';
    } catch (Exception $e) {
      Log::warning('Error Updating Marks', $e->getMessage());
      $status = 'error';
      $message = "Unable to Update Marks";
    }
    return response()->json(['status' => $status, 'message' => $message]);
  }

  public function updateCoConsolidatedValue(Request $request, $id)
  {
    $status = "";
    $message = "";

    $request->validate([
      'co_consolidated_value' => ['required'],
      'co_avarage_value' => ['required']
    ]);

    try {
      $data = Studentmark::find($id);
      if (!is_null($data)) {
        $data->co = json_encode($request->co_consolidated_value);
        $data->consolidated_co = $request->co_avarage_value;
        $data->save();
      } else {
        return (0);
      }
      $status = 'success';
      $message = 'Indirect Marks FeedBack Updated Successfully';
    } catch (Exception $e) {
      Log::warning('Error Updating FeedBack', $e->getMessage());
      $status = 'error';
      $message = "Unable to Update FeedBack";
    }
    return response()->json(['status' => $status, 'message' => $message]);
  }

  public function deleteStudentMarks(Request $request, $id)
  {
    $status = "";
    $message = "";

    try {
      if ($data = Studentmark::find($id)) {
        $data->delete();
      }
      $status = 'success';
      $message = 'Students Marks Added Successfully';
    } catch (Exception $e) {
      Log::warning('Error Deleting Marks', $e->getMessage());
      $status = 'error';
      $message = "Unable To Delete Marks";
    }
    return response()->json(['status' => $status, 'message' => $message]);
  }

  public function storeSyllabus(Request $request, $id)
  {
    $status = "";
    $message = "";


    try {

      if ($request->hasFile('syllabus')) {
        $completeCurriculumFileName = $request->file('syllabus')->getClientOriginalName();
        $CurriculumfileNameOnly = pathinfo($completeCurriculumFileName, PATHINFO_FILENAME);
        $CurriculumfileExtension = $request->file('syllabus')->getClientOriginalExtension();
        $Curriculumfile = str_replace(' ', '_', $CurriculumfileNameOnly) . '-' . rand() . '_' . time() . '.' . $CurriculumfileExtension;
        $CurriculumfilePath = $request->file('syllabus')->storeAs('public/syllabus', $Curriculumfile);
      }

      $test = Studentmark::find($id);
      $test->co = $CurriculumfilePath;
      $test->save();

      $status = "success";
      $message = "Files Added Successfully";
    } catch (Exception $e) {
      Log::warning('Error Deleting Marks', $e);
      $status = 'error';
      $message = "Unable To Add Files";
    }
    return response()->json(["status" => $status, "message" => $message]);
  }

  public function readSyllabus($id)
  {
    $data = Studentmark::find($id);
    if ($data) {
      $path = storage_path('app/' . $data->co);
      return response()->file($path);
    }
    return response()->json("No File Found", 404);
  }

  public function sendSubjectsForMapping()
  {
    $Data = Studentmark::where(
      'staff_code', 
      auth()->user()->department_number
    )->get();
    return response()->json($Data);
  }

  public function postCOPSOmapping(Request $request)
  {
    $status = "";
    $message = "";
    $request->validate([
      'co_id' => 'required',
      'psocos' => 'required'
    ]);

    try{
      Assessment_copso::create([
        'id' => Str::uuid(),
        'co_id' => $request->co_id,
        'direct_attainment' => json_encode($request['psocos'])
      ]);
      $status = "success";
      $message="Successfully Mapping Uploaded";
    }catch(Exception $e){
      $status = "error";
      $message="Unable to Upload Mapping";
    }

    return response()->json(['status'=>$status, 'message'=>$message]);
  }

  public function getCOPSOmapping($id)
  {

    $copso = DB::select('SELECT direct_attainment FROM assessment_copsos WHERE (co_id = :courseOUTCOME_id)', ['courseOUTCOME_id' => $id]);
    // $copsoIndirect = DB::select('SELECT indirect_assessment FROM assessment_copsos WHERE (co_id = :courseOUTCOME_id)', ['courseOUTCOME_id' => $id]);
    $copsoIndirect = null;
    if ($copso == null && $copsoIndirect == null) {
      $copso = array();
      $copsoIndirect = array();
      return response()->json(['copsos' => $copso, 'indirect_assessment'=>$copsoIndirect]);
    } else {
      $mapping = $copso[0];
      foreach ($mapping as $key => $value) {
        $Key =  $value;
      }
      $Key = json_decode($Key, true);
    }
    if($copsoIndirect == null){
      $copsoIndirect = array();
      return response()->json(['copsos' => $Key, 'indirect_assessment'=>$copsoIndirect]);
    }
    else{
      $indirect = $copsoIndirect[0];
      foreach ($indirect as $key => $value) {
        $indirect_copso =  $value;
      }
      $copso_indirect_read = json_decode($indirect_copso, true);
      return response()->json(['copsos' => $Key, 'indirect_assessment'=>$copso_indirect_read]);
    }
  }

  public function editCOPSOmapping(Request $request)
  {
    $status = "";
    $message = "";
    $request->validate([
      'co_id' => 'required',
      'psocos' => 'required'
    ]);

    try{
      $copso = Assessment_copso::where('co_id', $request->co_id)->get();
      $copso[0]->direct_attainment = json_encode($request->psocos);
      $copso[0]->save();
      $status = "success";
      $message="Successfully Mapping Updated";
    }catch(Exception $e){
      $status = "error";
      $message="Unable to Update Mapping";
    }
    return response()->json(['status' => $status, 'message' => $message]);
  }

  public function editCOPSOdirectAssessment(Request $request)
  {
    $status = "";
    $message = "";
    $request->validate([
      'co_id' => 'required',
      'direct_assessment' => 'required',
      'direct_assessment_avarage' => 'required'
    ]);

    try{
      $copso = Assessment_copso::where('co_id', $request->co_id)->get();
      $copso[0]->copso = json_encode($request->direct_assessment);
      $copso[0]->copso_avarage = $request->direct_assessment_avarage;
      $copso[0]->save();
      $status = "success";
      $message="Successfully DirectAssessment Updated";
    }catch(Exception $e){
      $status = "error";
      $message="Unable to Update DirectAssessment";
    }
    return response()->json(['status' => $status, 'message' => $message]);
  }
  
  public function editCOPSOIndirectAssessment(Request $request)
  {
    $status = "";
    $message = "";
    $request->validate([
      'co_id' => 'required',
      'indirect_assessment_upload' => 'required',
      'indirectAssessment' => 'required'
    ]);

    try{
      $copso = Assessment_copso::where('co_id', $request->co_id)->get();
      $copso[0]->indirect_assessment = json_encode($request->indirect_assessment_upload);
      $copso[0]->indirect_attainment = json_encode($request->indirectAssessment);
      $copso[0]->save();
      $status = "success";
      $message="Successfully InDirectAssessment Updated";
    }catch(Exception $e){
      $status = "error";
      $message="Unable to Update InDirectAssessment";
    }
    return response()->json(['status' => $status, 'message' => $message]);
  }

  public function editCOPSOIndirectAssessmentAvg(Request $request)
  {
    $status = "";
    $message = "";
    $request->validate([
      'co_id' => 'required',
      'consolidate_copso' => 'required',
      'consolidate_avg' => 'required'
    ]);

    try{
      $copso = Assessment_copso::where('co_id', $request->co_id)->get();
      $copso[0]->consolidated_copso = json_encode($request->consolidate_copso);
      $copso[0]->copso_avarage = $request->consolidate_avg;
      $copso[0]->save();
      $status = "success";
      $message="Successfully InDirectAssessment Updated";
    }catch(Exception $e){
      $status = "error";
      $message="Unable to Update InDirectAssessment";
    }
    return response()->json(['status' => $status, 'message' => $message]);
  }

  public function editCOPOIndirectAssessmentAvg(Request $request)
  {
    $status = "";
    $message = "";
    $request->validate([
      'co_id' => 'required',
      'consolidate_copo' => 'required',
      'consolidate_avg' => 'required'
    ]);

    try{
      $copso = Assessment_copo::where('co_id', $request->co_id)->get();
      $copso[0]->consolidated_copo = json_encode($request->consolidate_copo);
      $copso[0]->copo_avarage = $request->consolidate_avg;
      $copso[0]->save();
      $status = "success";
      $message="Successfully InDirectAssessment Updated";
    }catch(Exception $e){
      $status = "error";
      $message="Unable to Update InDirectAssessment";
    }
    return response()->json(['status' => $copso[0], 'message' => $message]);
  }
  

  public function deleteCOPSOIndirectAssessment(Request $request, $co_id)
  {
    $status = "";
    $message = "";

    try{
      $copso = Assessment_copso::where('co_id', $co_id)->get();
      $copso[0]->indirect_assessment = null;
      $copso[0]->indirect_attainment = null;
      $copso[0]->save();
      $status = "success";
      $message="Successfully InDirectAssessment Deleted";
    }catch(Exception $e){
      $status = "error";
      $message="Unable to Deleted InDirectAssessment";
    }
    return response()->json(['status' => $status, 'message' => $message]);
  }

  public function copsoReport(Request $request, $co_id)
  { 
    $copso = DB::select('SELECT copso FROM assessment_copsos WHERE (co_id = :courseOUTCOME_id)', ['courseOUTCOME_id' => $co_id]);
    // $copsoIndirect = DB::select('SELECT indirect_attainment FROM assessment_copsos WHERE (co_id = :courseOUTCOME_id)', ['courseOUTCOME_id' => $co_id]);
    $copsoIndirect = null;
    if ($copso == null && $copsoIndirect == null) {
      $copso = array();
      $copsoIndirect = array();
      return response()->json(['direct_assessment' => $copso, 'indirect_assessment'=>$copsoIndirect]);
    } else {
      $mapping = $copso[0];
      foreach ($mapping as $key => $value) {
        $Key =  $value;
      }
      $Key = json_decode($Key, true);
    }
    if($copsoIndirect == null){
      $copsoIndirect = array();
      return response()->json(['direct_assessment' => $Key, 'indirect_assessment'=>$copsoIndirect]);
    }
    else{
      $indirect = $copsoIndirect[0];
      foreach ($indirect as $key => $value) {
        $indirect_copso =  $value;
      }
      $copso_indirect_read = json_decode($indirect_copso, true);
      return response()->json(['direct_assessment' => $Key, 'indirect_assessment'=>$copso_indirect_read]);
    }
  }


  public function postCOPOmapping(Request $request)
  {
    $status = "";
    $message = "";
    $request->validate([
      'co_id' => 'required',
      'pocos' => 'required'
    ]);

    try{
      Assessment_copo::create([
        'id' => Str::uuid(),
        'co_id' => $request->co_id,
        'direct_attainment' => json_encode($request['pocos'])
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
    // $copoIndirect = DB::select('SELECT indirect_assessment FROM assessment_copos WHERE (co_id = :courseOUTCOME_id)', ['courseOUTCOME_id' => $id]);
    $copoIndirect = null;
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
      $copo[0]->copo_avarage = $request->direct_assessment_avarage;
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
    // $copoIndirect = DB::select('SELECT indirect_attainment FROM assessment_copos WHERE (co_id = :courseOUTCOME_id)', ['courseOUTCOME_id' => $co_id]);
    $copoIndirect = null;
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


  public function getPrograms()
  {
    $status = "";
    $message = "";


    $department = Department::find(auth()->user()->department_id);
    $copo = Studentmark::where('department_name', $department['department_name'])->get();


    return response()->json($copo);
  }

  public function getDataForCoPsoDirectAvarage($co_id, $regulation, $course_code, $section)
  {
    $copso = DB::select(
                          'SELECT direct_attainment FROM assessment_copsos WHERE (co_id = :courseOUTCOME_id)', 
                          ['courseOUTCOME_id' => $co_id]
                    );
    $mapping_copso = $copso[0];
    foreach ($mapping_copso as $key_copso => $value_copso) {
      $Key_copso =  $value_copso;
    }
    $Key_copso = json_decode($Key_copso, true);

    $examMark = DB::select(
                                'SELECT co FROM studentmarks 
                                    WHERE 
                                      (regulation = :academicYear) AND 
                                      (staff_code = :empCode) AND 
                                      (course_code = :courseID) AND 
                                      (section = :section)
                                ', [
                                    'academicYear' => $regulation, 
                                    'empCode' => auth()->user()->department_number, 
                                    'courseID' => $course_code, 
                                    'section' => $section
                                  ]
                          );
    $mapping_examMark = $examMark[0];
      foreach ($mapping_examMark as $key_examMark => $value_examMark) {
        $Key_examMark =  $value_examMark;
      }
      $Key_examMark = json_decode($Key_examMark, true);
    
    return response()->json(['co_direct_attainment' => $Key_examMark, 'copso_direct_attainment' => $Key_copso]);
  }

  public function getDataForCoPoDirectAvarage($co_id, $regulation, $course_code, $section)
  {
    $copo = DB::select(
                          'SELECT direct_attainment FROM assessment_copos WHERE (co_id = :courseOUTCOME_id)', 
                          ['courseOUTCOME_id' => $co_id]
                    );
    $mapping_copo = $copo[0];
    foreach ($mapping_copo as $key_copo => $value_copo) {
      $Key_copo =  $value_copo;
    }
    $Key_copo = json_decode($Key_copo, true);

    $examMark = DB::select(
                                'SELECT co FROM studentmarks 
                                    WHERE 
                                      (regulation = :academicYear) AND 
                                      (staff_code = :empCode) AND 
                                      (course_code = :courseID) AND 
                                      (section = :section)
                                ', [
                                    'academicYear' => $regulation, 
                                    'empCode' => auth()->user()->department_number, 
                                    'courseID' => $course_code, 
                                    'section' => $section
                                  ]
                          );
    $mapping_examMark = $examMark[0];
      foreach ($mapping_examMark as $key_examMark => $value_examMark) {
        $Key_examMark =  $value_examMark;
      }
      $Key_examMark = json_decode($Key_examMark, true);
    
    return response()->json(['co_direct_attainment' => $Key_examMark, 'copo_direct_attainment' => $Key_copo]);
  }


}
