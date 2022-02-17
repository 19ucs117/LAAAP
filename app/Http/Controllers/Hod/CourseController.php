<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\course_code;
use App\Models\SyllabusAssign;
use App\Models\User;
use App\Models\SyllabusCourses;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(['auth', 'hod']);
    }

    public function getCourseHod(Request $request, $departmentId){
        $courses = DB::table('course_codes')
                      ->whereRaw('department_id = ? AND id NOT IN (SELECT course_id FROM syllabus_assigns)', [$departmentId])
                      ->get();
        return response()->json($courses);
        // $matching = ['course_codes.department_id' => $departmentId, 'course_assigned_toStaff' => 0];
        // // $courses=course_code::select('course_code','course_title','course_title','credits','hours','category','semester')->get();
        // $courses = course_code::join('departments', 'departments.id', '=', 'course_codes.department_id')
        //     ->join('programs', 'programs.id', '=', 'course_codes.program_id')
        //     ->select('course_codes.id','programs.id as program_id','programs.department_id','departments.department_name','programs.program_name','course_codes.course_code','course_codes.course_title','course_codes.credits','course_codes.hours','course_codes.category','course_codes.semester')
        //     ->where($matching)
        //     ->get();
    }

    public function getAllCoursesHod($departmentId){
        $matching = ['course_codes.department_id' => $departmentId];
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

    public function getStaffDetail(Request $request, $departmentId){
      $matching = ['is_active' => 'yes', 'department_id' => $departmentId];
      $staffDetail = User::select('id', 'department_number', 'name')->where($matching)->get();
      return response()->json($staffDetail);
    }

    public function assignCourse(Request $request)
    {
        $status = "";
        $message = "";

        $request->validate([
            'course_id' => ['required'],
            'staff_id' => ['required'],
        ]);
        try {
            SyllabusAssign::create([
                'id' => Str::uuid(),
                'course_id' => $request->course_id,
                'user_id' => $request->staff_id,
                'assigned_by' => auth()->user()->id,
            ]);

            $status = 'success';
            $message = "Staff Assigned Successfully";
        } catch (Exception $e) {
            Log::warning('Error Assigning Staff', $e->getMessage());
            $status = 'error';
            $message = "Unable to Assign Staff";

        }
        return response()->json(['status' => $status, 'message' => $message]);
    }

    public function getAssignedStaff(Request $request, $departmentId)
    {
        $data=SyllabusAssign::join('course_codes', 'course_codes.id', '=', 'syllabus_assigns.course_id')
            ->join('users', 'users.id', '=', 'syllabus_assigns.user_id')
            ->select('course_codes.id as course_id','users.id as user_id','course_codes.course_code','course_codes.course_title', 'course_codes.credits', 'course_codes.hours', 'course_codes.category', 'course_codes.semester', 'users.name', 'syllabus_assigns.id')
            ->where('course_codes.department_id',$departmentId)
            ->get();

            return response()->json($data);
    }

    public function editAssignedCourses(Request $request, $tableId)
    {
      $status = "";
        $message = "";

        $request->validate([
            'course_id' => 'required',
            'staff_id' => 'required',
        ]);
        try {
            if ($SyllabusAssign = SyllabusAssign::find($tableId)) {
              $SyllabusAssign->course_id  = $request->course_id;
              $SyllabusAssign->user_id    = $request->staff_id;
              $SyllabusAssign->save();
            }
            $status = 'success';
            $message = "Staff Assignment Updated Successfully";
        } catch (Exception $e) {
            Log::warning('Error Updating Staff Assignment', $e->getMessage());
            $status = 'error';
            $message = "Unable to Update Staff Assignment";

        }
        return response()->json(['status' => $status, 'message' => $message]);
    }

    public function deleteAssignSyllabus(Request $request, $tableId)
    {
      $status="";
      $message="";
      try {
          if ($SyllabusAssign = SyllabusAssign::find($tableId)) {
            $SyllabusAssign->delete();
          }
          $status = 'success';
          $message = "Staff Deleted Successfully";
      } catch (Exception $e) {
          Log::warning('Error Deleting Staff',$e->getMessage());
          $status = 'error';
          $message = "Unable to Delete Staff";

      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    // public function getSyllabusCourse()
    // {
    //     $courses = SyllabusCourses::select('id', 'course_title', 'course_title', 'credits', 'hours', 'category', 'semester')->get();
    //     return response()->json($courses);
    // }

}
