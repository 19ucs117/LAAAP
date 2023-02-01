<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\course_code;
use App\Models\SyllabusCourses;

use App\Models\school;
use App\Models\Department;
use App\Models\program;
use App\Models\User;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(['auth', 'superadmin']);
    }

     public function addSchool(Request $request)
     {
       $status="";
       $message="";
       $request->validate([
           'school_name' => ['required', 'regex:/^[a-zA-Z ]*$/'],
       ]);

       try {
           school::create([
               'id' => Str::uuid(),
               'school_name' => strtoupper(trim($request->school_name)),
           ]);
           $status = 'success';
           $message = "School Added Successfully";
       } catch (Exception $e) {
           Log::warning('Error Adding School',$e->getMessage());
           $status = 'error';
           $message = "Unable to Add School";
       }

       return response()->json(['status' => $status,'message'=>$message]);
     }

     public function getSchool(Request $request)
     {
       $schools=school::select('id', 'school_name')->where('school_name', '!=', 'Loyola Database')
                                                    ->get();
       // $schools=school::find($id);
       return response()->json($schools);
     }

     public function editSchool(Request $request, $schoolId)
     {
       $status="";
       $message="";
       $request->validate([
           'school_name' => ['required'],
       ]);
       try {

           $schools = school::find($schoolId);
             // $schools = school::find($id);
             // $matching = ['id' => $schoolId];
             // $schools = school::where($matching);
             // if ($schools) {
             //   $schools->school_name = $request->school_name;
             //   $schools->save();
             //   return response()->json($schools);
             // }

           $schools->school_name = trim(strtoupper($request->input('school_name')));
           $schools->save();

           $status = 'success';
           $message = "SchoolName Updated Successfully";
       } catch (Exception $e) {
           Log::warning('Error Updating SchoolName',$e->getMessage());
           $status = 'error';
           $message = "Unable to Update SchoolName";

       }

       return response()->json(['status' => $status,'message'=>$message]);


     }

     public function deleteSchool(Request $request, $schoolId)
     {
       $status="";
       $message="";
       try {
           if ($schools = school::find($schoolId)) {
             // $matching = ['id' => $schoolId];
             // $schools = school::where($matching);
             // if ($schools) {
             //   $schools->delete();
             //   $schools->save();
             //   return response()->json($schools);
             // }
             $schools->delete();
           }
           $status = 'success';
           $message = "School Deleted Successfully";
       } catch (Exception $e) {
           Log::warning('Error Deleting School',$e->getMessage());
           $status = 'error';
           $message = "Unable to Delete School";

       }

       return response()->json(['status' => $status,'message'=>$message]);
     }

     public function addDepartmentAndProgram(Request $request)
     {
       $department_id = "";
        $status = "";
        $warning = "";
        $message = "";
        //validations
        $request->validate([
            'school_id' => 'required',
            'department_name' => 'required',
            'programs' => 'required'
        ]);
        //creating department id
    try{
        $department_id = Str::uuid();

        Department::create([
            'id' => $department_id,
            'school_id' => $request['school_id'],
            'department_name' => strtoupper(trim($request['department_name'])),
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        $sizeofprogramms = sizeof($request->programs);

        for($i = 0;$i < $sizeofprogramms; $i++)
        {
            Program::create([
                'id' => Str::uuid(),
                'school_id' => $request['school_id'],
                'department_id' => $department_id,
                'program_name' => strtoupper(trim($request->programs[$i]['program_name'])),
            ]);
        }
         $status = 'success';
         $message = "Department and programs Added Successfully";
    }
        catch (Exception $e) {
            Log::warning('Error Adding programm or department',$e->getMessage());
            $status = 'error';
            $message = "Unable to Add either program or department ";
       }
      return response()->json(['status' => $status,'message'=>$message]);

     }

     public function addProgram(Request $request)
     {
         $status = "";
         $message = "";
         $request->validate([
             'school_id' => 'required',
             'department_id' => 'required',
             'program_name' => 'required'
         ]);
         try {
             Program::create([
                 'id' => Str::uuid(),
                 'school_id' => $request->school_id,
                 'department_id' => $request->department_id,
                 'program_name' => strtoupper(trim($request->program_name)),
             ]);
             $status = 'success';
             $message = "Program Added Successfully";
         } catch (Exception $e) {
             $status = 'error';
             $message = $e->getMessage();
         }
         return response()->json(['status' => $status, 'message' => $message, 'program' => $request->program_name]);
     }

     public function getDepartmentName()
     { //('school_id', 'departmentId', 'department_name')
         $department_data = [];
         $department = department::select('school_id', 'id', 'department_name')
             ->where('department_name', '!=', 'Admin')
             ->where('department_name', '!=', 'SuperAdmin')
             ->get();

         foreach ($department as $val) {
             $program = program::select('id', 'program_name')->where('department_id', $val->id)->get();
             $department_data[] = [
                 'id' => $val->id,
                 'school_id' => $val->school_id,
                 'department_name' => $val->department_name,
                 'program' => $program,
             ];
         }
         return response()->json($department_data);
     }

     public function getDepartmentsOfSchool($schoolId)
     {
         $getDepartments = Department::where('school_id',$schoolId)->get();
         return response()->json($getDepartments);
     }

     public function editDepartment(Request $request)
     {
         $status = "";
         $message = "";
         $request->validate([
             'department_name' => ['required'],
         ]);
         try {
             if ($department = Department::find($request->id)) {
                 $department->department_name = trim(strtoupper($request->department_name));
                 $department->updated_by = auth()->user()->id;
                 $department->save();

                 foreach ($request->programs as $programData) {
                     if ($program = program::find($programData['id'])) {
                         $program->program_name = trim(strtoupper($programData['program_name']));
                         $program->save();
                     }
                 }
             }
             $status = 'success';
             $message = "Department And Programs Updated Successfully";
         } catch (Exception $e) {
             $status = 'error';
             $message = $e->getMessage();
         }

         return response()->json(['status' => $status, 'message' => $message]);
     }

     public function deleteDepartment(Request $request, $departmentId)
     {
       $status="";
       $message="";
       try {
           if ($department = Department::find($departmentId)) {
             $department->delete();
           }
           $status = 'success';
           $message = "Department Deleted Successfully";
       } catch (Exception $e) {
           Log::warning('Error Deleting Department',$e->getMessage());
           $status = 'error';
           $message = "Unable to Delete Department";

       }

       return response()->json(['status' => $status,'message'=>$message]);
     }

     public function getDepartmentAndProgram(Request $request, $departmentId)
     {
       // $program = program::select('school_id', 'id', 'department_id')->get();
       $program = program::join('departments', 'departments.id', '=', 'programs.department_id')
           ->join('schools', 'schools.id', '=', 'programs.school_id')
           ->select('schools.school_name','departments.department_name','programs.id','programs.program_name')
           ->where('programs.department_id', $departmentId)
           ->get();
       return response()->json($program);
     }

     public function editProgram(Request $request, $programId)
     {
         $status = "";
         $message = "";
         $request->validate([
             'programs_name' => 'required',
             'degrees_name' => 'required',
         ]);
         try {
             if ($program = program::find($programId)) {
                 $program->program_name = $request->program_name;
                 $program->degree = $request->degree;
                 $program->save();
             }
             $status = 'success';
             $message = "Program Updated Successfully";
         } catch (Exception $e) {
             Log::warning('Error Updating ProgramName', $e->getMessage());
             $status = 'error';
             $message = "Unable to Update ProgramName";
         }

         return response()->json(['status' => $status, 'message' => $message]);
     }

     public function deleteProgram(Request $request, $programId)
     {
       $status="";
       $message="";
       try {
           if ($program = program::find($programId)) {
             $program->delete();
           }
           $status = 'success';
           $message = "Program Deleted Successfully";
       } catch (Exception $e) {
           Log::warning('Error Deleting Program',$e->getMessage());
           $status = 'error';
           $message = "Unable to Deleting Program";

       }

       return response()->json(['status' => $status,'message'=>$message]);
     }

     public function addUser(Request $request)
     {
       $status = "";
       $message = "";
       $request->validate([
           'name' => ['required'],
           'email' => ['required'],
           'department_number' => ['required'],
           'department_id' => ['required'],
           'phone_number' => ['required'],
           'role_id' => ['required'],
       ]);
       try {
           $isActive = 'yes';
           $isDeleted = 'no';
           User::create([
               'id' => Str::uuid(),
               'department_number' => strtoupper(trim($request->department_number)),
               'department_id' => $request->department_id,
               'is_active' => $isActive,
               'name' => $request->name,
               'email' => $request->email,
               'phone_number' => $request->phone_number,
               'password' => Hash::make('Password@123'),
               'role_id' => $request->role_id,
               'is_deleted' => $isDeleted,
           ]);
           $status = 'success';
           $message = "User Added Successfully";
       } catch (Exception $e) {
           Log::warning('Error Adding User', $e->getMessage());
           $status = 'error';
           $message = "Unable to Add User";

       }
       return response()->json(['status' => $status, 'message' => $message]);
     }

     public function getUser()
     {
       try {
           $user = User::join('departments', 'departments.id', '=', 'users.department_id')
               ->join('roles', 'roles.id', '=', 'users.role_id')
               ->select('departments.department_name', 'roles.role_name','roles.id as role_id', 'users.id', 'users.name','users.email','users.phone_number','users.department_number','departments.id as department_id')
               ->where('users.role_id', '!=', '1')
               ->get();
           return response()->json(['status' => 'success', 'message' => $user]);

       } catch (Exception $e) {
           return response()->json(['status' => 'error', 'message' => $e->getmessage()]);
     }
   }

     public function edituserSuperAdmin(Request $request)
     {
       $status="";
       $message="";
       $request->validate([
         'id' => ['required'],
         'name' => ['required'],
         'email' => ['required'],
         'department_number' => ['required'],
         'department_id' => ['required'],
         'phone_number' => ['required'],
         'role_id' => ['required'],
       ]);
       try {
           if ($user = User::find($request->id)) {
             $user->department_number = $request->department_number;
             $user->department_id     = $request->department_id;
             $user->name              = $request->name;
             $user->email             = $request->email;
             $user->phone_number      = $request->phone_number;
             $user->role_id           = $request->role_id;
             $user->save();
           }
           $status = 'success';
           $message = "User Updated Successfully";
       } catch (Exception $e) {
           Log::warning('Error Updating User',$e);
           $status = 'error';
           $message = "Unable to Update User";

       }

       return response()->json(['status' => $status,'message'=>$message]);
     }

     public function deleteUser(Request $request, $userId)
     {
       $status="";
       $message="";
       try {
           if ($department = User::find($userId)) {
             $department->delete();
           }
           $status = 'success';
           $message = "User Deleted Successfully";
       } catch (Exception $e) {
           Log::warning('Error Deleting User',$e->getMessage());
           $status = 'error';
           $message = "Unable to Delete User";

       }

       return response()->json(['status' => $status,'message'=>$message]);
     }

     public function getCourse(){
         // $courses=course_code::select('course_code','course_title','course_title','credits','hours','category','semester')->get();
         $courses = course_code::join('departments', 'departments.id', '=', 'course_codes.department_id')
             ->join('programs', 'programs.id', '=', 'course_codes.program_id')
             ->select('course_codes.id','departments.id as department_id','programs.id as program_id','departments.department_name','programs.program_name','course_codes.course_code','course_codes.course_title','course_codes.credits','course_codes.hours','course_codes.category','course_codes.semester')
             ->get();
         return response()->json($courses);
     }

}
