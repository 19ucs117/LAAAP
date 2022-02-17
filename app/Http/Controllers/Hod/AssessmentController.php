<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

use App\Models\course_code;
use App\Models\Assignstaff;


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
        }
        $status = "success";
        $message = "Assigned Staff Updated Successfully";
      } catch (Exception $e) {
        log::warning('Error Updating Assigned Staff');
        $status = "error";
        $message = "Unable To Update Assigned Staff";
      }
      return response()->json(['status' => $status, 'message' => $message]);
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
}
