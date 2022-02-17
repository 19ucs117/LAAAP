<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

use App\Models\AssignStaff;
use App\Models\selected_subject;
use App\Models\Exammark;


class AssessmentController extends Controller
{
    //
    public function __construct()
    {
      $this->middleware(['auth', 'staff']);
    }

    public function getCoursesWithStaffId($staff_id)
    {
      $assignedCourses = AssignStaff::join('course_codes','course_codes.id', '=', 'assignstaffs.course_id')
                  ->select('course_codes.id  as course_id', 'course_codes.course_code', 'course_codes.course_title',
                  'assignstaffs.id', 'assignstaffs.section', 'assignstaffs.department_id', 'assignstaffs.program_id')
                  ->where('user_id', $staff_id)
                  ->get();
      return response()->json($assignedCourses);
    }

    public function getStudentsWithStaffIdAndSection($staff_id, $section, $course_id)
    {
      $matching = ['assignstaffs.user_id' => $staff_id, 'assignstaffs.course_id' => $course_id, 'student_details.section' => $section, 'selected_subjects.course_id' => $course_id];
      $studentDetails = selected_subject::join('course_codes','course_codes.id', '=', 'selected_subjects.course_id')
                  ->join('student_details', 'student_details.id', '=', 'selected_subjects.student_id')
                  ->join('assignstaffs', 'assignstaffs.department_id', '=', 'course_codes.department_id')
                  ->select('course_codes.id  as course_id', 'course_codes.course_code', 'course_codes.course_title',
                           'student_details.name','student_details.department_number', 'selected_subjects.id', 'selected_subjects.student_id',
                           'assignstaffs.user_id')
                  ->where($matching)
                  ->orderBy('student_details.department_number')
                  ->get();
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
              'course_id' => $request->course_id,
              'section' => $request->section,
              'assessment' => json_encode($request->markEntry),
              'saved_by' => auth()->user()->id,
            ]);
        
        $status = "success";
        $message = $request->examType." Marks Successfully Added";
      } catch (Exception $e) {
        log::warning('Error Adding Marks');
        $status = $e;
        $message = "Unable to Add Marks";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function getMarks($section, $course_id)
    {
      $matching = ['section'=>$section, 'course_id'=>$course_id];
      $examMarkDetails = Exammark::select('id', 'department_id', 'course_id', 'section')
                          ->where($matching)
                          ->get();
      $KeyValues = array();
      $Key = array();
          $examMark = DB::select('SELECT assessment FROM exammarks WHERE (course_id = :courseid) AND (section = :sectionName)', ['sectionName'=>$section, 'courseid'=>$course_id]);
      if ($examMark == null) {
        $examMark = array();
        return response()->json(['assessment'=>$examMark, 'subDetails'=>$examMarkDetails]);
      }
      $mapping = $examMark[0];

      foreach ($mapping as $key => $value) {
        $Key =  $value;
      }
      $Key = json_decode($Key, true);
      for ($i = 0; $i < sizeof($Key); $i++) {
        $KeyValues[$i] = $Key[$i];
      }
      return response()->json(['assessment'=>$KeyValues, 'subDetails'=>$examMarkDetails]);
    }


    public function updateMarks(Request $request, $examMarkID)
    {
      $status="";
      $message="";
      $request->validate([
          'markEntry' => 'required',
      ]);
      try {
          if($examMarks = Exammark::find($examMarkID))
          {
              $examMarks->assessment = $request->markEntry;
              $examMarks->save();
          }

          $status = 'success';
          $message = "Marks Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating Marks',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update Marks";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

}
