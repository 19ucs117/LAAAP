<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\course_code;
use App\Models\school;
use App\Models\User;
use App\Models\program;
use App\Models\Role;
use App\Models\batch_detail;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Else_;
use SebastianBergmann\Type\NullType;

class AddcourseController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(['auth', 'AddCourse']);
    }

    public function getRoles()
    {
        return response()->json(Role::select('id', 'role_name')->where('role_name', '!=', 'Super Admin')->get());
    }

    public function addcourse(Request $request)
    {
        $status="";
        $message="";
        $request->validate([
            'course_code' => ['required'],
            'department_id'=>['required'],
            'program_id'=>['required'],
            'course_title' => ['required'],
            'credits' => ['required'],
            'hours' => ['required'],
            'category' => ['required'],
            'semester' => ['required'],
        ]);
        try {
            course_code::create([
                'id' => Str::uuid(),
                'department_id'=>$request->department_id,
                'program_id'=>$request->program_id,
                'course_code' => strtoupper(trim($request->course_code)),
                'course_title' => strtoupper(trim($request->course_title)),
                'credits' => $request->credits,
                'hours' => $request->hours,
                'category' => $request->category,
                'semester' => $request->semester,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]);
            $status = 'success';
            $message = "Course Added Successfully";
        } catch (Exception $e) {
            Log::warning('Error Adding Course',$e->getMessage());
            $status = 'error';
            $message = "Unable to Add Course";

        }

        return response()->json(['status' => $status,'message'=>$message]);
    }

     public function editCourse(Request $request, $courseId)
     {
       $status="";
       $message="";
       $request->validate([
           'course_code' => ['required'],
           'department_id'=>['required'],
           'program_id'=>['required'],
           'course_title' => ['required'],
           'credits' => ['required'],
           'hours' => ['required'],
           'category' => ['required'],
           'semester' => ['required'],
       ]);
       try {
           $courses = course_code::find($courseId);
           if ($courses) {
             $courses->course_code    = strtoupper(trim($request->course_code));
             $courses->course_title   = strtoupper(trim($request->course_title));
             $courses->department_id  = $request->department_id;
             $courses->program_id     = $request->program_id;
             $courses->credits        = $request->credits;
             $courses->hours          = $request->hours;
             $courses->category       = $request->category;
             $courses->semester       = $request->semester;
             $courses->created_by     = auth()->user()->id;
             $courses->updated_by     = auth()->user()->id;
             $courses->save();
           }

           $status = 'success';
           $message = "Course Updated Successfully";
       } catch (Exception $e) {
           Log::warning('Error Updating Course',$e->getMessage());
           $status = 'error';
           $message = "Unable to Update Course";

       }
       return response()->json(['status' => $status,'message'=>$message]);
     }

     public function deleteCourse(Request $request, $coursesId)
     {
       $status="";
       $message="";
       try {
           if ($courses = course_code::find($coursesId)) {
             $courses->delete();
           }
           $status = 'success';
           $message = "Course Deleted Successfully";
       } catch (Exception $e) {
           Log::warning('Error Deleting Course',$e->getMessage());
           $status = 'error';
           $message = "Unable to Delete Course";

       }

       return response()->json(['status' => $status,'message'=>$message]);
     }



               //*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\
               //*\                                                             //*\
               //*\           PART - II    =>    ASSESSMENT                     //*\
               //*\                                                             //*\
               //*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\



     public function getUniqueBatchDetail($department_id, $program_id = null)
     {
         if($program_id != null){
            $matching = ["department_id" => $department_id, "program_id" => $program_id];
            $batchDetails = batch_detail::where($matching)->get();
         }
         else{
            $batchDetails = batch_detail::where('department_id', $department_id)->get();
         }

         $batchDetails = $batchDetails->unique('batchNo');
        

       // foreach ($batchDetails as $val) {
       //
       //     $course_details = selected_subject::join('programs', 'programs.id', '=', 'batch_details.program_id')
       //                               ->join('departments', 'departments.id', '=','batch_details.department_id')
       //                               ->join('schools', 'schools.id', '=', 'departments.school_id')
       //                               ->select('schools.id as school_id', 'schools.school_name' ,'departments.id as department_id',
       //                                        'departments.department_name', 'programs.id as program_id', 'programs.program_name')
       //                               ->get();
       //     $studentSelectedSubjects[] = [
       //         'id' => $val->id,
       //         'department_number' => $val->department_id,
       //         'name' => $val->program_id,
       //         'section'=> $val->batchNo,
       //         'department_id' => $val->NoSections,
       //         'program_id' => $val->program_id,
       //         'courses' => $course_details->department_name,
       //         'program_name'=> $course_details->program_name,
       //         'department_name'=>$course_details->school_id,
       //         'school_id' => $course_details->school_name,
       //     ];
       // }
       // return response()->json($studentSelectedSubjects);
       // $batch = batch_detail::join('programs', 'programs.id', '=', 'batch_details.program_id')
       //                     ->join('departments', 'departments.id', '=','batch_details.department_id')
       //                     ->join('schools', 'schools.id', '=', 'departments.school_id')
       //                     ->select('schools.id as school_id', 'schools.school_name' ,'departments.id as department_id',
       //                              'departments.department_name', 'programs.id as program_id', 'programs.program_name',
       //                              'batch_details.id', 'batch_details.batchNo', 'batch_details.NoSections')
       //                     ->groupBy('batch_details.batchNo')
       //                     ->get();
       return response()->json($batchDetails);
     }

     public function getBatchDetails()
     {
       $batch = batch_detail::join('programs', 'programs.id', '=', 'batch_details.program_id')
                           ->join('departments', 'departments.id', '=','batch_details.department_id')
                           ->join('schools', 'schools.id', '=', 'departments.school_id')
                           ->select('schools.id as school_id', 'schools.school_name' ,'departments.id as department_id',
                                    'departments.department_name', 'programs.id as program_id', 'programs.program_name',
                                    'batch_details.id', 'batch_details.batchNo', 'batch_details.NoSections')
                           ->get();
       return response()->json($batch);
     }

}
