<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;


use App\Models\batch_detail;
use App\Models\Course;
use App\Models\selected_subject;
use App\Models\student_detail;
use App\Models\course_code;
use App\Models\Department;
use App\Models\Exammark;


class AssessmentController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(['auth', 'superadmin']);
    }

    public function addBatch(Request $request)
    {
      $status="";
      $message="";

      $request->validate([
        'department_id' => ['required'],
        'program_id' => ['required'],
        'batchNo' => ['required'],
        'noSections' => ['required'],
      ]);

      try {
        batch_detail::create([
            'id' => Str::uuid(),
            'department_id' => $request->department_id,
            'program_id' => $request->program_id,
            'batchNo' => trim($request->batchNo),
            'NoSections' => $request->noSections,
        ]);
        $status = 'success';
        $message = "Batch Added Successfully";
      } catch (Exception $e) {
        log::warning('Error Adding Batch Details',$e->getMessage());
        $status = 'error';
        $message = "Unable to Add Batch";
      }

      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function updateBatchDetails(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'id' => ['required'],
        'batchNo' => ['required'],
        'sections' => ['required'],
        'program_id'=>['required'],
        'department_id'=>['required'],
      ]);
      try {
        $batchDetails = batch_detail::find($request->id);
        if (!is_null($batchDetails)) {
          $batchDetails->department_id = $request->department_id;
          $batchDetails->program_id = $request->program_id;
          $batchDetails->batchNo = $request->batchNo;
          $batchDetails->noSections = $request->sections;
          $batchDetails->save();
        }
        $status = "success";
        $message = "Batch Details Updated Successfully";
      } catch (Exception $e) {
        log::warning('Error Updating Batch Details', $e->getMessage());
        $status = "error";
        $message = "Unable To Update Batch Details";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function deleteBatchDetails($batchId)
    {
      $status = "";
      $message = "";
      try {
        $batchDetails = batch_detail::find($batchId);
        if (!is_null($batchDetails)) {
          $batchDetails->delete();
        }
        $status = "success";
        $message = "Batch Details Deleted Successfully";
      } catch (Exception $e) {
        log::warning('Error Deleting Batch Details',$e->getMessage());
        $status = "error";
        $message = "Unable To Delete Batch";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function getAllCoursesWithProgramID($program_id, $semesterNo=null){
        if($semesterNo != null){
            $matching = ['course_codes.program_id' => $program_id, 'course_codes.semester' => $semesterNo];
        }
        else{
            $matching = ['course_codes.program_id' => $program_id];
        }

        // $courses=course_code::select('course_code','course_title','course_title','credits','hours','category','semester')
        //     ->where($matching)
        //     ->get();
        $courses = course_code::join('departments', 'departments.id', '=', 'course_codes.department_id')
            ->join('programs', 'programs.id', '=', 'course_codes.program_id')
            ->select('course_codes.id as value','course_codes.course_title as label')
            ->where($matching)
            ->get();

        return response()->json($courses);
    }

    public function addStudentsAndSelectedSubject(Request $request)
    {
      $status="";
      $message="";

      $request->validate([
        'department_id' => ['required'],
        'program_id' => ['required'],
        'batch_id' => ['required'],
        'department_number' => ['required'],
        'name' => ['required'],
        'section' => ['required'],
        'courses'=>['required'],
      ]);

      try{
          $student_id = Str::uuid();
          student_detail::create([
              'id' => $student_id,
              'department_id' => $request->department_id,
              'program_id' => $request->program_id,
              'batch_id' => $request->batch_id,
              'departmentNumber' => $request->department_number,
              'name' => strtoupper(trim($request->name)),
              'section'=> strtoupper(trim($request->section)),
          ]);

          $sizeofcourses = sizeof($request->courses);

          for($i = 0;$i < $sizeofcourses; $i++)
          {
              selected_subject::create([
                  'id' => Str::uuid(),
                  'student_id' => $student_id,
                  'course_id' => $request->courses[$i],
              ]);
          }
           $status = 'success';
           $message = "Student And Selected Subject Added Successfully";
      } catch (Exception $e) {
        log::warning('Error Adding Student Details ',$e);
        $status = 'error';
        $message = "Unable to Add Student And His/Her Selected Subject";
      }

      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function getSelectedSubjects()
    {
      $studentSelectedSubjects = [];
      $studentDetails = student_detail::join('programs', 'programs.id', '=','student_details.program_id')
                                      ->join('departments', 'departments.id', '=', 'student_details.department_id')
                                      ->join('schools', 'schools.id', '=', 'departments.school_id')
                                      ->join('batch_details', 'batch_details.id', '=', 'student_details.batch_id')
                                      ->select('batch_details.batchNo','student_details.id', 'student_details.departmentNumber','student_details.name',
                                               'student_details.section', 'departments.id as department_id','departments.department_name',
                                               'programs.id as program_id', 'programs.program_name', 'school_name', 'departments.school_id')
                                      ->orderBy('student_details.departmentNumber')
                                      ->get();

      foreach ($studentDetails as $val) {

          $course_details = selected_subject::join('course_codes', 'course_codes.id', '=', 'selected_subjects.course_id')
                                            ->select('course_codes.id as value', 'course_codes.course_title as label')
                                            ->where('student_id', $val->id)->get();
          $studentSelectedSubjects[] = [
              'id' => $val->id,
              'department_number' => $val->departmentNumber,
              'batchNo' => $val->batchNo,
              'name' => $val->name,
              'section'=> $val->section,
              'courses' => $course_details,
              'department_id' => $val->department_id,
              'program_id' => $val->program_id,
              'program_name'=> $val->program_name,
              'department_name'=>$val->department_name,
              'school_id' => $val->school_id,
              'school_name'=> $val->school_name,
          ];
      }
      return response()->json($studentSelectedSubjects);
    }

    public function updateSelectedSubjects(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'department_id' => ['required'],
        'program_id' => ['required'],
        'department_number' => ['required'],
        'courses'=>['required'],
        'name' => ['required'],
        'section' => ['required'],
      ]);
      try {
          if ($student_details = student_detail::find($request->id)) {
              $student_details->department_id = $request->department_id;
              $student_details->program_id  = $request->program_id;
              $student_details->departmentNumber = strtoupper(trim($request->department_number));
              $student_details->name  = strtoupper(trim($request->name));
              $student_details->section = strtoupper(trim($request->section));
              $student_details->save();

              $courseDetails = selected_subject::where('student_id',$request->id)->get();
              $requiredCourses = array();
              foreach($courseDetails as $totalSubOfSem){
                array_push($requiredCourses, $totalSubOfSem->course_id);
              }
              $courseDelete = array_diff($requiredCourses, $request->courses);

              $nullcourses = array();
              foreach ($request->courses as $coursesData) {
                  $matching = ['student_id'=>$request->id, 'course_id'=>$coursesData];
                  $subjects = selected_subject::where($matching)->first();
                  if($subjects == null)
                  {
                    array_push($nullcourses, $coursesData);
                  }
              }
              if(isset($nullcourses)){
                foreach($nullcourses as $newCourse)
                {
                  selected_subject::create([
                      'id' => Str::uuid(),
                      'student_id' => $request->id,
                      'course_id' => $newCourse,
                  ]);
                }
              }
              if(isset($courseDelete)){
                $cia1 = array();
                foreach($courseDelete as $CourseToDelete)
                {
                    $matching = ['student_id'=>$request->id, 'course_id'=>$CourseToDelete];
                    $examMarks = DB::select('SELECT assessment FROM exammarks WHERE (course_id = :courseid) AND (section = :sectionName)', ['sectionName'=>$student_details->section, 'courseid'=>$CourseToDelete]);

                    if($examMarks != null){
                        $examMarksID = DB::select('SELECT id FROM exammarks WHERE (course_id = :courseid) AND (section = :sectionName)', ['sectionName'=>$student_details->section, 'courseid'=>$CourseToDelete]);
                        $external = array();
                        $externalMarkEntry = $examMarks[0];
                        foreach ($externalMarkEntry as $key => $value) {
                            $external =  $value;
                        }
                        $external = json_decode($external, true);
                        for($i=0; $i<sizeof($external);$i++){
                            $studentToDelete = array_search($student_details->department_number, $external[$i]);
                            if($studentToDelete){
                                $indexOfArray = $i;
                            }
                        }
                        unset($external[$indexOfArray]);
                        if($externalMarkEntryProxy = Exammark::find($examMarksID[0]->id))
                        {
                            $externalMarkEntryProxy->assessment = $external;
                            $externalMarkEntryProxy->save();
                        }
                    }
                    selected_subject::where($matching)->delete();
                }
              }

          }
          $status = 'success';
          $message = "StudentDetails And SubjectDetails Updated Successfully";
      } catch (Exception $e) {
        log::warning('Error Updating Student Data');
        $status = "error";
        $message = $e;
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function deleteStudentDetails(Request $request)
    {
      $status = "";
      $message = "";
      $student_details = array();
      try {
          if ($student_details = student_detail::join('selected_subjects','selected_subjects.student_id','=','student_details.id')->where('student_details.id', $request->id)->get()) { 
            foreach($student_details as $value){
              $examMarks = DB::select('SELECT assessment FROM exammarks WHERE (section = :sectionName) AND (batch_id = :batchID)', ['sectionName'=>$value['section'], 'batchID'=> $value['batch_id'], 'course_id' => $value['course_id']]);
            }  
            // $examMarks = DB::select('SELECT assessment FROM exammarks WHERE (section = :sectionName)', ['sectionName'=>$student_details->section]);

              // if($examMarks != null){
              //     $examMarksID = DB::select('SELECT id FROM externals WHERE (section = :sectionName)', ['sectionName'=>$student_details->section]);
              //     $external = array();
              //     $externalMarkEntry = $examMarks[0];
              //     foreach ($externalMarkEntry as $key => $value) {
              //         $external =  $value;
              //     }
              //     $external = json_decode($external, true);
              //     for($i=0; $i<sizeof($external);$i++){
              //         $studentToDelete = array_search($student_details->department_number, $external[$i]);
              //         if($studentToDelete){
              //             $indexOfArray = $i;
              //         }
              //     }
              //     unset($external[$indexOfArray]);
              //     if($externalMarkEntryProxy = Exammark::find($examMarksID[0]->id))
              //     {
              //         $externalMarkEntryProxy->assessment = $external;
              //         $externalMarkEntryProxy->save();
              //     }
              // }
              // $student_details->delete();
          }
          $status = 'success';
          $message = "StudentDetails Deleted Successfully";
      } catch (Exception $e) {
        log::warning('Error Updating Student Data');
        $status = "error";
        $message = $e;
      }
      return response()->json(['status' => $student_details, 'message' => $message]);
    }

    public function getDepartmentNProgram($programName, $departmentName)
    {
        $coConsolidated = array();
        $courses = DB::select('SELECT consolidated_co FROM studentmarks WHERE (program_name = :program) AND (department_name = :department) ORDER BY consolidated_co DESC', ['program' => $programName, 'department' => $departmentName]);
        for ($i = 0; $i < sizeof($courses); $i++) {
            $EACHcourse = $courses[$i];
            foreach ($EACHcourse as $keys => $values) {
                $COJsonKey =  $values;
            }
            $CO = json_decode($COJsonKey, true);
            array_push($coConsolidated, $CO);
        }
        $coConsolidated = collect($coConsolidated)->filter();

        return response()->json($coConsolidated);
    }

    public function getCourses($programName, $departmentName)
    {
        $coConsolidated = array();
        $courses = DB::select('SELECT consolidated_co FROM studentmarks WHERE (program_name = :program) AND (department_name = :department) ORDER BY consolidated_co DESC', ['program' => $programName, 'department' => $departmentName]);
        for ($i = 0; $i < sizeof($courses); $i++) {
            $EACHcourse = $courses[$i];
            foreach ($EACHcourse as $keys => $values) {
                $COJsonKey =  $values;
            }
            $CO = json_decode($COJsonKey, true);
            array_push($coConsolidated, $CO);
        }
        $coConsolidated = collect($coConsolidated)->filter();

        return response()->json($coConsolidated);
    }

}
