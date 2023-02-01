<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::prefix('auth')->group(function () {
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
    Route::post('/me', [\App\Http\Controllers\AuthController::class, 'me']);
    Route::post('/role', [\App\Http\Controllers\AuthController::class, 'role']);
    Route::post('/isLogin', [\App\Http\Controllers\AuthController::class, 'isLogin']);
    Route::post('/edit-user', [\App\Http\Controllers\AuthController::class, 'editUser']);
    Route::post('/change-password', [\App\Http\Controllers\AuthController::class, 'editPassword']);
});

Route::middleware(['superadmin', 'auth'])->group(function () {
    Route::post('/add-vission-mission', [\App\Http\Controllers\SuperAdmin\VissionMissionController::class, 'addVissionMission']);
    Route::put('/edit-vission-mission/{id}', [\App\Http\Controllers\SuperAdmin\VissionMissionController::class, 'editVissionMission']);
    Route::delete('/delete-vission-mission/{id}', [\App\Http\Controllers\SuperAdmin\VissionMissionController::class, 'deleteVissionMission']);
    Route::post('/add-peo', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'addPeo']);
    Route::put('/edit-peo', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'editPeo']);
    Route::delete('/delete-peo/{id}/{labelNo}', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'deletePeo']);
    Route::post('/add-po', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'addPo']);
    Route::put('/edit-po', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'editPo']);
    Route::delete('/delete-po/{id}/{labelNo}', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'deletePo']);
    Route::post('/add-school', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'addSchool']);
    Route::get('/get-school', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'getSchool']);
    Route::put('/edit-school/{id}', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'editSchool']);
    Route::delete('/delete-school/{id}', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'deleteSchool']);
    Route::post('/add-departmentNprogram', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'addDepartmentAndProgram']);
    Route::post('/add-program', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'addProgram']);
    Route::get('/get-departments-school/{school}', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'getDepartmentsOfSchool']);
    Route::get('/get-Department', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'getDepartmentName']);
    Route::put('/edit-department', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'editDepartment']);
    Route::delete('/delete-department/{id}', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'deleteDepartment']);
    Route::put('/edit-program', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'editProgram']);
    Route::delete('/delete-program/{id}', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'deleteProgram']);
    Route::post('/add-user', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'addUser']);
    Route::get('/get-user', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'getUser']);
    Route::post('/edit-user-superAdmin', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'edituserSuperAdmin']);
    Route::delete('/delete-user/{id}', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'deleteUser']);
    Route::get('/get-courseList', [\App\Http\Controllers\SuperAdmin\CourseController::class, 'getCourse']);
    // Route::post('/add-peopoMapping', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'addPeoPoMapping']);
    // Route::get('/get-peopoMapping/{school_id}', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'getPeoPoMapping']);
    // Route::put('/edit-peopoMapping/{id}', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'editPeoPoMapping']);
    Route::post('/add-visionandmissionpeoMapping', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'addVisionAndMissionPeoMapping']);
    Route::get('/get-visionandmissionpeoMapping/{school_id}', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'getVisionAndMissionPeoMapping']);
    Route::put('/edit-visionandmissionpeoMapping/{id}', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'editVisionAndMissionPeoMapping']);
    Route::post('/add-peopoMappingJustification', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'addPeoPoMappingJustification']);
    Route::put('/edit-peopoMappingJustification/{id}', [\App\Http\Controllers\SuperAdmin\MappingController::class, 'editPeoPoMappingJustification']);
    // File Upload
    Route::post('/add-department-csv', [\App\Http\Controllers\SuperAdmin\FileuploadController::class, 'addDepartmentCsv']);
    Route::post('/add-program-csv', [\App\Http\Controllers\SuperAdmin\FileuploadController::class, 'addProgramCsv']);
    Route::post('/add-user-csv', [\App\Http\Controllers\SuperAdmin\FileuploadController::class, 'addUserCsv']);
});


Route::middleware(['AddCourse', 'auth'])->group(function () {
    Route::post('/add-course', [\App\Http\Controllers\SuperAdmin\AddcourseController::class, 'addCourse']);
    Route::put('/edit-course/{id}', [\App\Http\Controllers\SuperAdmin\AddcourseController::class, 'editCourse']);
    Route::delete('/delete-course/{id}', [\App\Http\Controllers\SuperAdmin\AddcourseController::class, 'deleteCourse']);
    Route::get('/getRoles', [\App\Http\Controllers\SuperAdmin\AddcourseController::class, 'getRoles']);
    Route::post('/add-course-csv', [\App\Http\Controllers\SuperAdmin\FileuploadController::class, 'addcourse']);
});


Route::middleware(['hod', 'auth'])->group(function () {
    Route::get('/get-courseList-hod/{department_id}', [\App\Http\Controllers\Hod\CourseController::class, 'getCourseHod']);
    Route::get('/get-All-courseList-hod/{department_id}', [\App\Http\Controllers\Hod\CourseController::class, 'getAllCoursesHod']);
    Route::get('/get-staffDetails-hod/{department_id}', [\App\Http\Controllers\Hod\CourseController::class, 'getStaffDetail']);
    Route::post('/assign-course', [\App\Http\Controllers\Hod\CourseController::class, 'assignCourse']);
    Route::get('/get-assignedCourses/{department_id}', [\App\Http\Controllers\Hod\CourseController::class, 'getAssignedStaff']);
    Route::put('/edit-assignedCourses/{id}', [\App\Http\Controllers\Hod\CourseController::class, 'editAssignedCourses']);
    // Route::delete('/delete-assignedCourses/{id}/{course_id}', [\App\Http\Controllers\Hod\CourseController::class, 'deleteAssignSyllabus']);
    Route::delete('/delete-assignedCourses/{id}', [\App\Http\Controllers\Hod\CourseController::class, 'deleteAssignSyllabus']);
    Route::post('/add-pso', [\App\Http\Controllers\Hod\MappingController::class, 'addPso']);
    Route::put('/edit-pso', [\App\Http\Controllers\Hod\MappingController::class, 'editPso']);
    Route::delete('/delete-pso/{id}/{labelNo}', [\App\Http\Controllers\Hod\MappingController::class, 'deletePso']);
    Route::post('/add-popsoMapping', [\App\Http\Controllers\Hod\MappingController::class, 'addPoPsoMapping']);
    Route::get('/get-popsoMapping/{program_id}/{school_id}', [\App\Http\Controllers\Hod\MappingController::class, 'getPoPsoMapping']);
    Route::put('/edit-popsoMapping/{id}', [\App\Http\Controllers\Hod\MappingController::class, 'editPoPsoMapping']);
    Route::post('/add-peopsoMapping', [\App\Http\Controllers\Hod\MappingController::class, 'addPeoPsoMapping']);
    Route::get('/get-peopsoMapping/{program_id}/{school_id}', [\App\Http\Controllers\Hod\MappingController::class, 'getPeoPsoMapping']);
    Route::put('/edit-peopsoMapping/{id}', [\App\Http\Controllers\Hod\MappingController::class, 'editPeoPsoMapping']);
});

Route::middleware(['staff', 'auth'])->group(function () {
    Route::get('/get-course-byId/{id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'getCourseById']);
    Route::get('/get-staff-course/{user_id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'getStaffCourse']);
    Route::post('/add-courseOverview', [\App\Http\Controllers\Staff\CurriculumController::class, 'addCourseOverview']);
    // Route::post('/add-courseOverview/{id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'addCourseOverview']);
    Route::get('/get-courseOverview/{course_id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'getCourseOverview']);
    Route::put('/edit-courseOverview/{id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'editCourseOverview']);
    Route::post('/add-courseObjective', [\App\Http\Controllers\Staff\CurriculumController::class, 'addCourseObjective']);
    Route::get('/get-courseObjective/{course_id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'getCourseObjective']);
    Route::put('/edit-courseObjective/{id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'editCourseObjective']);
    Route::post('/add-coursePrerequisite', [\App\Http\Controllers\Staff\CurriculumController::class, 'addCoursePrerequisite']);
    Route::get('/get-coursePrerequisite/{course_id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'getCoursePrerequisite']);
    Route::put('/edit-coursePrerequisite/{id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'editCoursePrerequisite']);
    Route::post('/add-co', [\App\Http\Controllers\Staff\CurriculumController::class, 'addCo']);
    Route::get('/get-co/{course_id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'getCo']);
    Route::put('/edit-co', [\App\Http\Controllers\Staff\CurriculumController::class, 'editCo']);
    Route::delete('/delete-co/{id}/{labelNo}', [\App\Http\Controllers\Staff\CurriculumController::class, 'deleteCo']);
    Route::post('/add-units', [\App\Http\Controllers\Staff\CurriculumController::class, 'addUnits']);
    Route::get('/get-units/{course_id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'getUnits']);
    Route::put('/edit-units', [\App\Http\Controllers\Staff\CurriculumController::class, 'editUnits']);
    Route::delete('/delete-units/{id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'deleteUnits']);
    Route::post('/add-courseTextBooks', [\App\Http\Controllers\Staff\CurriculumController::class, 'addCourseTextBooks']);
    Route::get('/get-courseTextBooks/{course_id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'getCourseTextBooks']);
    Route::put('/edit-courseTextBooks/{id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'editCourseTextBooks']);
    Route::post('/add-courseReferenceBooks', [\App\Http\Controllers\Staff\CurriculumController::class, 'addCourseReferenceBooks']);
    Route::get('/get-courseReferenceBooks/{course_id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'getCourseReferenceBooks']);
    Route::put('/edit-courseReferenceBooks/{id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'editCourseReferenceBooks']);
    Route::post('/add-courseWebReferences', [\App\Http\Controllers\Staff\CurriculumController::class, 'addCourseWebReferences']);
    Route::get('/get-courseWebReferences/{course_id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'getCourseWebReferences']);
    Route::put('/edit-courseWebReferences/{id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'editCourseWebReferences']);
    Route::post('/add-copsoMapping', [\App\Http\Controllers\Staff\MappingController::class, 'addCoPsoMapping']);
    Route::get('/get-copsoMapping/{course_id}/{program_id}', [\App\Http\Controllers\Staff\MappingController::class, 'getCoPsoMapping']);
    Route::put('/edit-copsoMapping/{id}', [\App\Http\Controllers\Staff\MappingController::class, 'editCoPsoMapping']);
    Route::post('/add-copoMapping', [\App\Http\Controllers\Staff\MappingController::class, 'addPoCoMapping']);
    Route::get('/get-copoMapping/{course_id}/{school_id}', [\App\Http\Controllers\Staff\MappingController::class, 'getPoCoMapping']);
    Route::put('/edit-copoMapping/{id}', [\App\Http\Controllers\Staff\MappingController::class, 'editPoCoMapping']);
    Route::post('/add-copeoMapping', [\App\Http\Controllers\Staff\MappingController::class, 'addPeoCoMapping']);
    Route::get('/get-copeoMapping/{course_id}/{school_id}', [\App\Http\Controllers\Staff\MappingController::class, 'getPeoCoMapping']);
    Route::put('/edit-copeoMapping/{id}', [\App\Http\Controllers\Staff\MappingController::class, 'editPeoCoMapping']);
    Route::post('/add-copeoMappingJustification', [\App\Http\Controllers\Staff\MappingController::class, 'addCoPeoMappingJustification']);
    Route::put('/edit-copeoMappingJustification/{id}', [\App\Http\Controllers\Staff\MappingController::class, 'editCoPeoMappingJustification']);
    Route::post('/add-copoMappingJustification', [\App\Http\Controllers\Staff\MappingController::class, 'addCoPoMappingJustification']);
    Route::put('/edit-copoMappingJustification/{id}', [\App\Http\Controllers\Staff\MappingController::class, 'editCoPoMappingJustification']);
    Route::post('/add-copsoMappingJustification', [\App\Http\Controllers\Staff\MappingController::class, 'addCoPsoMappingJustification']);
    Route::put('/edit-copsoMappingJustification/{id}', [\App\Http\Controllers\Staff\MappingController::class, 'editCoPsoMappingJustification']);
    Route::get('summary/{id}/{programId}/{schoolId}', [\App\Http\Controllers\Staff\CurriculumController::class, 'curriculamSummary']);
    Route::post('/add-dynamicLessonPlan', [\App\Http\Controllers\Staff\CurriculumController::class, 'createDynamicLessonPlan']);
    Route::get('/get-dynamicLessonPlan/{course_id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'getDynamicLessonPlan']);
    Route::put('/edit-dynamicLessonPlan', [\App\Http\Controllers\Staff\CurriculumController::class, 'updateDynamicLessonPlan']);
    Route::delete('/delete-dynamicLessonPlan/{id}', [\App\Http\Controllers\Staff\CurriculumController::class, 'deleteDynamicLessonPlan']);
});

Route::middleware('auth')->group(function () {
    Route::get('/get-vision-mission', [\App\Http\Controllers\MappingController::class, 'getVissionMission']);
    Route::get('/get-peo/{school_id}', [\App\Http\Controllers\MappingController::class, 'getPeo']);
    Route::get('/get-po/{school_id}', [\App\Http\Controllers\MappingController::class, 'getPo']);
    Route::get('/get-pso/{program_id}', [\App\Http\Controllers\MappingController::class, 'getPso']);
    Route::get('/get-program/{department_id}', [\App\Http\Controllers\MappingController::class, 'getDepartmentAndProgram']);
    Route::get('/get-progressVisionMissionPeoMapping', [\App\Http\Controllers\SuperAdmin\MappingProgress::class, 'progressInVisionMissionPeoMapping']);
    Route::get('/get-progressPeoPoMapping', [\App\Http\Controllers\SuperAdmin\MappingProgress::class, 'progressInPeoPoMapping']);
    Route::get('/get-progressPsoPoMapping/{department_id}', [\App\Http\Controllers\SuperAdmin\MappingProgress::class, 'progressInPsoPoMapping']);
    Route::get('/get-progressPsoPeoMapping/{department_id}', [\App\Http\Controllers\SuperAdmin\MappingProgress::class, 'progressInPsoPeoMapping']);
    // Route::get('/get-progressCoPsoMapping/{course_id}', [\App\Http\Controllers\SuperAdmin\MappingProgress::class, 'progressInCoPsoMapping']);
    // Route::get('/get-progressCoPoMapping/{course_id}', [\App\Http\Controllers\SuperAdmin\MappingProgress::class, 'progressInCoPoMapping']);
    // Route::get('/get-progressCoPeoMapping/{course_id}', [\App\Http\Controllers\SuperAdmin\MappingProgress::class, 'progressInCoPeoMapping']);
});
/*



                                          *****************************************************************
                                          *                                                               *
                                          *                PART - II    =>    ASSESSMENT                  *
                                          *                                                               *
                                          *****************************************************************


*/
Route::middleware(['superadmin', 'auth'])->group(function () {
    Route::post('/add-batch', [\App\Http\Controllers\SuperAdmin\AssessmentController::class, 'addBatch']);
    Route::put('/edit-batch', [\App\Http\Controllers\SuperAdmin\AssessmentController::class, 'updateBatchDetails']);
    Route::delete('/delete-batch/{id}', [\App\Http\Controllers\SuperAdmin\AssessmentController::class, 'deleteBatchDetails']);
    Route::get('/get-Courses-WithProgramId/{program_id}/{semester?}', [\App\Http\Controllers\SuperAdmin\AssessmentController::class, 'getAllCoursesWithProgramID']);
    Route::post('/add-studentSelectedSubject', [\App\Http\Controllers\SuperAdmin\AssessmentController::class, 'addStudentsAndSelectedSubject']);
    Route::get('/get-studentSelectedSubject', [\App\Http\Controllers\SuperAdmin\AssessmentController::class, 'getSelectedSubjects']);
    Route::put('/update-studentSelectedSubject', [\App\Http\Controllers\SuperAdmin\AssessmentController::class, 'updateSelectedSubjects']);
    Route::delete('/delete-studentDetail/{id}', [\App\Http\Controllers\SuperAdmin\AssessmentController::class, 'deleteStudentDetails']);

    // File Upload
    Route::post('/add-batch-csv', [\App\Http\Controllers\SuperAdmin\FileuploadController::class, 'addBatchCSV']);
    Route::post('/add-student-csv', [\App\Http\Controllers\SuperAdmin\FileuploadController::class, 'addStudentsCSV']);
    Route::post('/add-courses-csv', [\App\Http\Controllers\SuperAdmin\FileuploadController::class, 'addSelectedCourseCSV']);
});


Route::middleware(['AddCourse', 'auth'])->group(function () {
    Route::get('/get-uniqueBatch/{department_id}/{program_id?}', [\App\Http\Controllers\SuperAdmin\AddcourseController::class, 'getUniqueBatchDetail']);
    Route::get('/get-batch', [\App\Http\Controllers\SuperAdmin\AddcourseController::class, 'getBatchDetails']);
});


Route::middleware(['hod', 'auth'])->group(function () {
    Route::get('/get-CoursesHod-WithProgramId/{program_id}/{semester}', [\App\Http\Controllers\Hod\AssessmentController::class, 'getAllCoursesHodWithProgramID']);
    Route::post('/add-assignStaff', [\App\Http\Controllers\Hod\AssessmentController::class, 'assignStaff']);
    Route::get('/get-assignStaff/{department_id}', [\App\Http\Controllers\Hod\AssessmentController::class, 'getSectionAssignedStaff']);
    Route::put('/edit-assignStaff/{id}', [\App\Http\Controllers\Hod\AssessmentController::class, 'updateAssignedStaff']);
    Route::delete('/delete-assignStaff/{id}', [\App\Http\Controllers\Hod\AssessmentController::class, 'deleteAssignedStaff']);
    Route::get('/psopeo-mapping/{program_name}', [\App\Http\Controllers\Hod\AttainmentController::class, 'programWisePEO']);
    Route::get('/get-school-Hod', [\App\Http\Controllers\Hod\AttainmentController::class, 'getUserSchool']);
    Route::post('/add-peopoMapping', [\App\Http\Controllers\Hod\AttainmentController::class, 'addPeoPoMapping']);
    Route::get('/get-peopoMapping/{school_id}', [\App\Http\Controllers\Hod\AttainmentController::class, 'getPeoPoMapping']);
    Route::put('/edit-peopoMapping/{id}', [\App\Http\Controllers\Hod\AttainmentController::class, 'editPeoPoMapping']);
    Route::get('/peopo-report/{school_id}', [\App\Http\Controllers\Attainment\UserController::class, 'Report']);
    Route::post('/peopso-mapping', [\App\Http\Controllers\Attainment\UserController::class, 'assessment_psopeos']);
    Route::get('/get-peopso-mapping/{program_name}', [\App\Http\Controllers\Attainment\UserController::class, 'getPeoPsosMapping']);
    Route::put('/edit-peopso-mapping/{mapping_id}', [\App\Http\Controllers\Attainment\UserController::class, 'updatePsoPeosMapping']);
});


Route::middleware(['staff', 'auth'])->group(function () {
    Route::get('/get-getCoursesWithStaffId/{user_id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'getCoursesWithStaffId']);
    Route::get('/get-students/{user_id}/{section}/{course_id}/{batchNo}', [\App\Http\Controllers\Staff\AssessmentController::class, 'getStudentsWithStaffIdAndSection']);
    Route::post('/add-marks', [\App\Http\Controllers\Staff\AssessmentController::class, 'addMarks']);
    Route::get('/get-marks/{section}/{course_id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'getMarks']);
    Route::put('/edit-marks/{id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'updateMarks']);


    /* ASSESSMENT PHASE - || */

    Route::post('/add-exammarks', [\App\Http\Controllers\Staff\AssessmentController::class, 'addStudentMarks']);
    Route::get('/get-academicYear', [\App\Http\Controllers\Staff\AssessmentController::class, 'getAcademicYear']);
    Route::get('/get-studentMarks/{academic_year}/{course_code}/{section}', [\App\Http\Controllers\Staff\AssessmentController::class, 'getStudentMarks']);
    Route::put('/update-marks/{mark_id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'updateStudentMarks']);
    Route::delete('/delete-Marks/{id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'deleteStudentMarks']);
    Route::put('/update-indirectCoMarks', [\App\Http\Controllers\Staff\AssessmentController::class, 'updateIndirectMarks']);
    Route::put('/update-indirectCoMarksFeedBack/{id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'updateIndirectMarksFeedback']);
    Route::put('/update-cosolidatedCo/{id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'updateCoConsolidatedValue']);
    Route::get('/get-completedCOs', [\App\Http\Controllers\Staff\AssessmentController::class, 'uploadFileFEEDBACk']);
    Route::get('/delete-indirectCo/{id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'deleteIndirectMarks']);
    Route::post('/add-syllabus/{id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'storeSyllabus']);
    Route::get('/read-syllabus/{id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'readSyllabus']);
    Route::get('/read-CoDirectMarks/{academic_year}/{course_code}/{section}', [\App\Http\Controllers\Staff\AssessmentController::class, 'getCoDirectAssessmentMarks']);
    Route::get('/getAllSubjectsOfAStaff', [\App\Http\Controllers\Staff\AssessmentController::class, 'sendSubjectsForMapping']);
    Route::post('/add-assessment-copso', [\App\Http\Controllers\Staff\AssessmentController::class, 'postCOPSOmapping']);
    Route::get('/get-assessment-copso/{co_id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'getCOPSOmapping']);
    Route::post('/edit-assessment-copso', [\App\Http\Controllers\Staff\AssessmentController::class, 'editCOPSOmapping']);
    Route::post('/edit-copso-DirectAssessment', [\App\Http\Controllers\Staff\AssessmentController::class, 'editCOPSOdirectAssessment']);
    Route::post('/edit-copso-inDirectAssessment', [\App\Http\Controllers\Staff\AssessmentController::class, 'editCOPSOIndirectAssessment']);
    Route::delete('/delete-copso-inDirectAssessment/{co_id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'deleteCOPSOIndirectAssessment']);
    Route::get('/copso-report/{co_id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'copsoReport']);
    Route::post('/add-assessment-copo', [\App\Http\Controllers\Staff\AssessmentController::class, 'postCOPOmapping']);
    Route::get('/get-assessment-copo/{co_id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'getCOPOmapping']);
    Route::post('/edit-assessment-copo', [\App\Http\Controllers\Staff\AssessmentController::class, 'editCOPOmapping']);
    Route::post('/edit-copo-DirectAssessment', [\App\Http\Controllers\Staff\AssessmentController::class, 'editCOPOdirectAssessment']);
    Route::post('/edit-copo-inDirectAssessment', [\App\Http\Controllers\Staff\AssessmentController::class, 'editCOPOIndirectAssessment']);
    Route::delete('/delete-copo-inDirectAssessment/{co_id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'deleteCOPOIndirectAssessment']);
    Route::get('/copo-report/{co_id}', [\App\Http\Controllers\Staff\AssessmentController::class, 'copoReport']);
    Route::post('/edit-copso-consolidatedAvg', [\App\Http\Controllers\Staff\AssessmentController::class, 'editCOPSOIndirectAssessmentAvg']);
    Route::post('/edit-copo-consolidatedAvg', [\App\Http\Controllers\Staff\AssessmentController::class, 'editCOPOIndirectAssessmentAvg']);

    Route::get('/get-data-calculate-copso-direct/{co_id}/{regulation}/{course_code}/{section}', [\App\Http\Controllers\Staff\AssessmentController::class, 'getDataForCoPsoDirectAvarage']);
    Route::get('/get-data-calculate-copo-direct/{co_id}/{regulation}/{course_code}/{section}', [\App\Http\Controllers\Staff\AssessmentController::class, 'getDataForCoPoDirectAvarage']);
}); 



/*



                                          *****************************************************************
                                          *                                                               *
                                          *                PART - III    =>    ATTAINMENT                 *
                                          *                                                               *
                                          *****************************************************************


*/

// Route::middleware(['superadmin', 'auth'])->group(function () {

// });


// Route::middleware(['AddCourse', 'auth'])->group(function () {

// });


Route::middleware(['hod', 'auth'])->group(function () {
    Route::get('/get-programs', [\App\Http\Controllers\Hod\AttainmentController::class, 'getPrograms']);
    Route::post('/get-courses', [\App\Http\Controllers\Hod\AttainmentController::class, 'getCourses']);
    Route::get('/get-pso-report/{program_name}/{academic_year}', [\App\Http\Controllers\Hod\AttainmentController::class, 'getCoPsoReport']);
    Route::get('/get-copso-avg/{academic_year}', [\App\Http\Controllers\Hod\AttainmentController::class, 'getAvgCoPso']);
    Route::get('/get-schools-copso-report', [\App\Http\Controllers\Hod\AttainmentController::class, 'sendSchoolsCopoReport']);
    Route::get('/get-copo-report/{school_id}/{academic_year}', [\App\Http\Controllers\Hod\AttainmentController::class, 'getCoPoReport']);
    Route::get('/get-copo-avg/{academic_year}', [\App\Http\Controllers\Hod\AttainmentController::class, 'getAvgCoPo']);
    Route::post('/create-pso-indirect', [\App\Http\Controllers\Hod\AssessmentController::class, 'createPSOindirectAssessment']);
    Route::post('/edit-pso-indirect', [\App\Http\Controllers\Hod\AssessmentController::class, 'editPSOindirectAssessment']);
    Route::get('/get-pso-indirect', [\App\Http\Controllers\Hod\AssessmentController::class, 'getPSOindirectAssessment']);
    Route::post('/delete-pso-indirect', [\App\Http\Controllers\Hod\AssessmentController::class, 'deletePSOindirectAssessment']);
    Route::get('/get-pso-indirect-assessment/{program_name}/{regulation}', [\App\Http\Controllers\Hod\AttainmentController::class, 'getPsoIndirect']);
    Route::get('/get-copso-avarage-on-directAssessment/{regulation}', [\App\Http\Controllers\Hod\AttainmentController::class, 'getPsoCoAvarageOnDirectAssessment']);
    Route::get('/get-copso-IndirectAssessment/{regulation}', [\App\Http\Controllers\Hod\AttainmentController::class, 'getPsoCoInDirectAssessment']);
    Route::post('/create-po-indirect', [\App\Http\Controllers\Hod\AssessmentController::class, 'createPOindirectAssessment']);
    Route::get('/get-po-indirect', [\App\Http\Controllers\Hod\AssessmentController::class, 'getPOindirectAssessment']);
    Route::post('/delete-po-indirect', [\App\Http\Controllers\Hod\AssessmentController::class, 'deletePOindirectAssessment']);
    Route::get('/get-copo-avarage-on-directAssessment/{regulation}', [\App\Http\Controllers\Hod\AttainmentController::class, 'getPoCoAvarageOnDirectAssessment']);
});


// Route::middleware(['staff', 'auth'])->group(function () {
    
    
// });
