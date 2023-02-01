<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\school;
use App\Models\Department;
use App\Models\program;
use App\Models\User;
use App\Models\course_code;
use App\Models\batch_detail;
use App\Models\student_detail;
use App\Models\selected_subject;

class FileuploadController extends Controller
{

    public function addDepartmentCsv(Request $request)
     {
         $status = "";
         $message = "";
         $request->validate([
             'department_name' => ['required', 'regex:/^[a-zA-Z ]*$/'],
             'school_name' => ['required', 'regex:/^[a-zA-Z ]*$/']
         ]);
         try {
            $schoolData = school::where('school_name', strtoupper(trim($request->school_name)))->get();
            if(isset($schoolData[0])){
                Department::create([
                    'id' => Str::uuid(),
                    'school_id' => $schoolData[0]->id,
                    'department_name' => strtoupper(trim($request->department_name)),
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
                ]);

                $status = 'success';
                $message = 'Departments Added Successfully';
            }else{
             $status = 'error';
             $message = $request->school_name.' Does Not Found!';
            }
         } catch (Exception $e) {
             $status = 'error';
             $message = $e->getMessage();
         }
         return response()->json(['status' => $status, 'message' => $message]);
     }

     public function addProgramCsv(Request $request)
     {
         $status = "";
         $message = "";
         $request->validate([
             'program_name' => ['required', 'regex:/^[a-zA-Z ]*$/'],
             'department_name' => ['required', 'regex:/^[a-zA-Z ]*$/'],
         ]);
         try {
            $departmentData = Department::where('department_name', strtoupper($request->department_name))->get();
            if(isset($departmentData[0])){
                Program::create([
                    'id' => Str::uuid(),
                    'school_id' => $departmentData[0]->school_id,
                    'department_id' => $departmentData[0]->id,
                    'program_name' => strtoupper(trim($request->program_name)),
                ]);

                $status = 'success';
                $message = "Program Added Successfully";
            }else{
                $status = 'error';
                $message = $request->department_name.' Does Not Found!';
            }

         } catch (Exception $e) {
             $status = 'error';
             $message = $request->$e->getMessage();
         }
         return response()->json(['status' => $status, 'message' => $message, 'program' => $request->program_name]);
     }

     public function addUserCsv(Request $request)
     {
       $status = "";
       $message = "";
       $request->validate([
           'name' => ['required', 'regex:/^[a-zA-Z ]*$/'],
           'email' => ['required', 'regex:/^.+@.+$/i'],
           'department_number' => ['required'],
           'department_name' => ['required', 'regex:/^[a-zA-Z ]*$/'],
           'phone_number' => ['required', 'size:10',],
           'role_id' => ['required', 'size:1'],
       ]);
       try {
            $isActive = 'yes';
            $isDeleted = 'no';
           $departmentData = Department::where('department_name', strtoupper($request->department_name))->get();
           if(isset($departmentData[0])){
            User::create([
                'id' => Str::uuid(),
                'department_number' => strtoupper(trim($request->department_number)),
                'department_id' => $departmentData[0]->id,
                'is_active' => $isActive,
                'name' => strtoupper(trim($request->name)),
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make('Password@123'),
                'role_id' => $request->role_id,
                'is_deleted' => $isDeleted,
            ]);

            $status = 'success';
            $message = 'User Added Successfully';
           }else{
               $status = 'error';
               $message = $request->department_name.' Does Not Found!';
           }

       } catch (Exception $e) {
           Log::warning('Error Adding User', $e->getMessage());
           $status = 'error';
           $message = $e->getMessage();

       }
       return response()->json(['status' => $status, 'message' => $message]);
     }

     public function addcourse(Request $request)
    {
        $status="";
        $message="";
        $request->validate([
            'course_code' => ['required'],
            'department_name'=>['required', 'regex:/^[a-zA-Z ]*$/'],
            'program_name'=>['required', 'regex:/^[a-zA-Z ]*$/'],
            'course_title' => ['required'],
            'credits' => ['required'],
            'hours' => ['required'],
            'category' => ['required'],
            'semester' => ['required', 'size:1'],
        ]);
        try {
            $programData = Program::where('program_name', strtoupper($request->program_name))->get();
            if(isset($programData[0])){
                course_code::create([
                    'id' => Str::uuid(),
                    'department_id'=>$programData[0]->department_id,
                    'program_id'=>$programData[0]->id,
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
            }else{
                $status = 'error';
                $message = $request->department_name.' Does Not Found!';
            }

        } catch (Exception $e) {
            Log::warning('Error Adding Course',$e->getMessage());
            $status = 'error';
            $message = $e->getMessage();

        }

        return response()->json(['status' => $status,'message'=>$message]);
    }



                    //*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\
                    //*\                                                            //*\
                    //*\                 PART - II    =>    ASSESSMENT              //*\
                    //*\                                                            //*\
                    //*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\//*\


    public function addBatchCSV(Request $request)
    {
        $status="";
        $message="";
        $request->validate([
            'department_name'=>['required', 'regex:/^[a-zA-Z ]*$/'],
            'program_name'=>['required', 'regex:/^[a-zA-Z ]*$/'],
            'batchNo' => ['required', 'regex:/^[0-9]{4}$/'],
            'noSections' => ['required'],
        ]);
        try {
            $programData = Program::where('program_name', strtoupper($request->program_name))->get();
            if(isset($programData[0])){
                batch_detail::create([
                    'id' => Str::uuid(),
                    'department_id' => $programData[0]->department_id,
                    'program_id' => $programData[0]->id,
                    'batchNo' => trim($request->batchNo),
                    'NoSections' => $request->noSections,
                ]);

                $status = 'success';
                $message = "Batch Added Successfully";
            }else{
                $status = 'error';
                $message = $request->department_name.' Does Not Found!';
            }

        } catch (Exception $e) {
            Log::warning('Error Adding Batch',$e->getMessage());
            $status = 'error';
            $message = $e->getMessage();

        }

        return response()->json(['status' => $status,'message'=>$message]);
    }

    public function addStudentsCSV(Request $request)
    {
        $status="";
        $message="";
        $request->validate([
            'department_name'=>['required', 'regex:/^[a-zA-Z ]*$/'],
            'program_name'=>['required', 'regex:/^[a-zA-Z ]*$/'],
            'department_number' => ['required'],
            'batch_number' => ['required'],
            'name' => ['required'],
            'section' => ['required'],
        ]);
        try {
            $matching = ['department_name'=>trim(strtoupper($request->department_name)), 'program_name'=>trim(strtoupper($request->program_name)), 'batchNo'=>$request->batch_number];
            $programData = Program::join('departments', 'departments.id', '=', 'programs.department_id')
                                    ->join('batch_details', 'batch_details.program_id', '=', 'programs.id')
                                    ->select('batch_details.id', 'batch_details.department_id', 'batch_details.program_id')
                                    ->where($matching)->get();
            if(isset($programData[0])){
                student_detail::create([
                    'id' => Str::uuid(),
                    'department_id' => $programData[0]->department_id,
                    'program_id' => $programData[0]->program_id,
                    'batch_id' => $programData[0]->id,
                    'departmentNumber' => strtoupper(trim($request['department_number'])),
                    'name' => strtoupper(trim($request['name'])),
                    'section'=> strtoupper(trim($request['section'])),
                ]);

                $status = 'success';
                $message = "Student Added Successfully";
            }else{
                $status = 'error';
                $message = $request->department_name.' Does Not Found!';
            }

        } catch (Exception $e) {
            Log::warning('Error Adding Student',$e->getMessage());
            $status = 'error';
            $message = 'Unable To Add Student';

        }

        return response()->json(['status' => $programData,'message'=>$message]);
    }

    public function addSelectedCourseCSV(Request $request)
    {
        $status="";
        $message="";
        $request->validate([
            'department_number'=>['required'],
            'course_code'=>['required'],
        ]);
        try {
            $studentData = student_detail::where('departmentNumber', trim(strtoupper($request->department_number)))->get();
            $courseData = course_code::where('course_code', trim(strtoupper($request->course_code)))->get();
            if(isset($studentData[0])){
                selected_subject::create([
                    'id' => Str::uuid(),
                    'student_id' => $studentData[0]->id,
                    'course_id' => $courseData[0]->id,
                ]);
                $status = 'success';
                $message = "Student Added Successfully";
            }else{
                $status = 'error';
                $message = $request->department_number.' Does Not Found!';
            }

        } catch (Exception $e) {
            Log::warning('Error Adding Student',$e->getMessage());
            $status = 'error';
            $message = $e;

        }

        return response()->json(['status' => $status,'message'=>$message]);
    }


}
