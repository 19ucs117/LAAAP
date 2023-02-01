<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCode;
use App\Models\Department;
use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use App\Models\school;
use App\Models\program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InitialSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sr=Str::uuid();
            $dep1 = Str::uuid(1);
            $dep2 = Str::uuid(1);
            $schoolId = Str::uuid(1);
            $programdep1 = Str::uuid(1);
            $programdep2 = Str::uuid(1);
            //
            $role = [[
                'role_name' => 'Super Admin',
                'created_by' => $sr,
                'updated_by' => $sr
            ], [
                'role_name' => 'Admin',
                'created_by' => $sr,
                'updated_by' => $sr
            ], [
                'role_name' => 'HOD',
                'created_by' => $sr,
                'updated_by' => $sr
            ], [
                'role_name' => 'Staff',
                'created_by' => $sr,
                'updated_by' => $sr
            ]];
            Role::insert($role);
            $school = [[
                'id' => $schoolId,
                'school_name' => 'Loyola Database',
            ]];
            school::insert($school);
            $department = [[
                'id' => $dep1,
                'school_id' => $schoolId,
                'department_name' => 'Admin',
                'created_by' => $sr,
                'updated_by' => $sr
            ], [
                'id' => $dep2,
                'school_id' => $schoolId,
                'department_name' => 'SuperAdmin',
                'created_by' => $sr,
                'updated_by' => $sr
            ]];
            Department::insert($department);
            $program = [[
                'id' => $programdep1,
                'school_id' => $schoolId,
                'department_id' => $dep1,
                'program_name' => 'AdminProgram'
            ], [
                'id' => $programdep2,
                'school_id' => $schoolId,
                'department_id' => $dep2,
                'program_name' => 'SuperAdminProgram'
            ]];
            program::insert($program);
            $users = [
                [
                    'id'=>Str::uuid(),
                    'department_number' => 'loyola01',
                    'department_id' => $dep1,
                    'is_active' => 'yes',
                    'name' => 'Dean of Academics',
                    'email' => 'dean@loyolacolege.edu',
                    'phone_number' => 1234567890,
                    'password' => bcrypt('superadmin'),
                    'role_id' => 1,
                    'profile' => 'dist/img/user-image.png'
                ],[
                    'id'=>Str::uuid(),
                    'department_number' => '19UCS117',
                    'department_id' => $dep2,
                    'is_active' => 'yes',
                    'name' => 'P VENKATA RAVIEKANTH',
                    'email' => '19ucs117@loyolacollege.edu',
                    'phone_number' => 9081112345,
                    'password' => bcrypt('ravie@2020'),
                    'role_id' => 1,
                    'profile' => 'dist/img/user-image.png'
                ]
            ];
            User::insert($users);

    }
}
