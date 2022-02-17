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
use App\Models\selected_subject;
use App\Models\student_detail;
use App\Models\course_code;
use App\Models\Cia1;
use App\Models\Cia2;
use App\Models\Component;
use App\Models\External;


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

    public function addSelectedSubject(Request $request)
    {
      $status="";
      $message="";

      $request->validate([
        'department_id' => ['required'],
        'program_id' => ['required'],
        'department_number' => ['required'],
        'courses'=>['required'],
        'name' => ['required'],
        'section' => ['required'],
      ]);

      try{
          $student_id = Str::uuid();
          student_detail::create([
              'id' => $student_id,
              'department_id' => $request['department_id'],
              'program_id' => $request['program_id'],
              'department_number' => strtoupper(trim($request['department_number'])),
              'name' => strtoupper(trim($request['name'])),
              'section'=> strtoupper(trim($request['section'])),
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
        log::warning('Error Adding Student Details ',$e->getMessage());
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
                                      ->select('student_details.id', 'student_details.department_number','student_details.name',
                                               'student_details.section', 'departments.id as department_id','departments.department_name',
                                               'programs.id as program_id', 'programs.program_name', 'school_name', 'departments.school_id')
                                      ->orderBy('student_details.department_number')
                                      ->get();

      foreach ($studentDetails as $val) {

          $course_details = selected_subject::join('course_codes', 'course_codes.id', '=', 'selected_subjects.course_id')
                                            ->select('course_codes.id as value', 'course_codes.course_title as label')
                                            ->where('student_id', $val->id)->get();
          $studentSelectedSubjects[] = [
              'id' => $val->id,
              'department_number' => $val->department_number,
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
              $student_details->department_number = strtoupper(trim($request->department_number));
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
                    $cia1Marks = DB::select('SELECT assessment FROM cia1s WHERE (course_id = :courseid) AND (section = :sectionName)', ['sectionName'=>$student_details->section, 'courseid'=>$CourseToDelete]);
                    $cia2Marks = DB::select('SELECT assessment FROM cia2s WHERE (course_id = :courseid) AND (section = :sectionName)', ['sectionName'=>$student_details->section, 'courseid'=>$CourseToDelete]);
                    $componentMarks = DB::select('SELECT assessment FROM components WHERE (course_id = :courseid) AND (section = :sectionName)', ['sectionName'=>$student_details->section, 'courseid'=>$CourseToDelete]);
                    $examMarks = DB::select('SELECT assessment FROM externals WHERE (course_id = :courseid) AND (section = :sectionName)', ['sectionName'=>$student_details->section, 'courseid'=>$CourseToDelete]);

                    if($cia1Marks != null){
                        $cia1MarksID = $examMarkCIA1 = DB::select('SELECT id FROM cia1s WHERE (course_id = :courseid) AND (section = :sectionName)', ['sectionName'=>$student_details->section, 'courseid'=>$CourseToDelete]);
                        $cia1 = array();
                        $cia1MarkEntry = $cia1Marks[0];
                        foreach ($cia1MarkEntry as $key => $value) {
                            $cia1 =  $value;
                        }
                        $cia1 = json_decode($cia1, true);
                        for($i=0; $i<sizeof($cia1);$i++){
                            $studentToDelete = array_search($student_details->department_number, $cia1[$i]);
                            if($studentToDelete){
                                $indexOfArray = $i;
                            }
                        }
                        unset($cia1[$indexOfArray]);
                        if($cia1MarkEntryProxy = Cia1::find($cia1MarksID[0]->id))
                        {
                            $cia1MarkEntryProxy->assessment = $cia1;
                            $cia1MarkEntryProxy->save();
                        }
                    }
                    if($cia2Marks != null){
                        $cia2MarksID = $examMarkCIA2 = DB::select('SELECT id FROM cia2s WHERE (course_id = :courseid) AND (section = :sectionName)', ['sectionName'=>$student_details->section, 'courseid'=>$CourseToDelete]);
                        $cia2 = array();
                        $cia2MarkEntry = $cia2Marks[0];
                        foreach ($cia2MarkEntry as $key => $value) {
                            $cia2 =  $value;
                        }
                        $cia2 = json_decode($cia2, true);
                        for($i=0; $i<sizeof($cia2);$i++){
                            $studentToDelete = array_search($student_details->department_number, $cia2[$i]);
                            if($studentToDelete){
                                $indexOfArray = $i;
                            }
                        }
                        unset($cia2[$indexOfArray]);
                        if($cia2MarkEntryProxy = Cia2::find($cia2MarksID[0]->id))
                        {
                            $cia2MarkEntryProxy->assessment = $cia2;
                            $cia2MarkEntryProxy->save();
                        }
                    }
                    if($componentMarks != null){
                        $componentMarksID = DB::select('SELECT id FROM components WHERE (course_id = :courseid) AND (section = :sectionName)', ['sectionName'=>$student_details->section, 'courseid'=>$CourseToDelete]);
                        $component = array();
                        $componentMarkEntry = $componentMarks[0];
                        foreach ($componentMarkEntry as $key => $value) {
                            $component =  $value;
                        }
                        $component = json_decode($component, true);
                        for($i=0; $i<sizeof($component);$i++){
                            $studentToDelete = array_search($student_details->department_number, $component[$i]);
                            if($studentToDelete){
                                $indexOfArray = $i;
                            }
                        }
                        unset($component[$indexOfArray]);
                        if($componentMarkEntryProxy = Component::find($componentMarksID[0]->id))
                        {
                            $componentMarkEntryProxy->assessment = $component;
                            $componentMarkEntryProxy->save();
                        }
                    }
                    if($examMarks != null){
                        $externalMarksID = DB::select('SELECT id FROM externals WHERE (course_id = :courseid) AND (section = :sectionName)', ['sectionName'=>$student_details->section, 'courseid'=>$CourseToDelete]);
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
                        if($externalMarkEntryProxy = External::find($cia1MarksID[0]->id))
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
      try {
          if ($student_details = student_detail::find($request->id)) {
              $student_details->delete();
          }
          $status = 'success';
          $message = "StudentDetails Deleted Successfully";
      } catch (Exception $e) {
        log::warning('Error Updating Student Data');
        $status = "error";
        $message = $e->errorInfo;
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

}
