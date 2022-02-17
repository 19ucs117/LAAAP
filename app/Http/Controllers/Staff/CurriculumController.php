<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;

use App\Models\SyllabusAssign;
use App\Models\course_code;
use App\Models\CourseOverview;
use App\Models\CourseObjective;
use App\Models\Prerequisite;
use App\Models\AddUnit;
use App\Models\TextBook;
use App\Models\ReferenceBook;
use App\Models\WebReference;
use App\Models\Co;
use App\Models\Copeo;
use App\Models\copso;
use App\Models\Poco;
use App\Models\dynamicLessonPlan;

class CurriculumController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(['auth', 'staff']);
    }

    public function getCourseById(Request $request, $courseId)
    {
      $getCourseById = course_code::join('programs', 'programs.id', '=', 'course_codes.program_id')
                                ->select('course_codes.trackingNo','course_codes.id','course_codes.course_code','course_codes.course_title','course_codes.credits','course_codes.hours','course_codes.category','course_codes.semester', 'programs.id as program_id')
                                ->where('course_codes.id', $courseId)
                                ->get();
      return response()->json($getCourseById);
    }

    public function getStaffCourse(Request $request, $userId)
    {
      $getStaffCourse = course_code::join('syllabus_assigns', 'syllabus_assigns.course_id', '=', 'course_codes.id')
                                ->join('programs', 'programs.id', '=', 'course_codes.program_id')
                                ->join('departments', 'departments.id', '=', 'course_codes.department_id')
                                ->select('course_codes.trackingNo','departments.department_name','programs.id as program_id','programs.program_name','course_codes.id','course_codes.course_code','course_codes.course_title','course_codes.credits','course_codes.hours','course_codes.category','course_codes.semester')
                                ->where('syllabus_assigns.user_id', $userId)
                                ->get();
      return response()->json($getStaffCourse);
    }

    // public function addCourseOverview(Request $request, $Id)
    // {
    //   $status="";
    //   $message="";
    //   $request->validate([
    //       'id' => ['required'],
    //       'department_id' => ['required'],
    //       'course_id' => ['required'],
    //       'courseOverview' => ['required'],
    //       'saved_by' => ['required'],
    //   ]);
    //   CourseOverview::upsert(
    //     [['id' => Str::uuid(),
    //     'department_id' => $request->department_id,
    //     'course_id' => $request->course_id,
    //     'courseOverview' => $request->courseOverview,
    //     'saved_by' => auth()->user()->id,
    //     ]],
    //     [$Id],
    //     ['courseOverview', 'saved_by']
    //   );
    // }

    public function addCourseOverview(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
          'department_id' => ['required'],
          'course_id' => ['required'],
          'courseOverview' => ['required']
      ]);
      try {
          CourseOverview::create([
                'id' => Str::uuid(),
                'department_id' => $request->department_id,
                'course_id' => $request->course_id,
                'courseOverview' => $request->courseOverview,
                'saved_by' => auth()->user()->id,
          ]);
          $course = course_code::find($request->course_id);
          if ($course->trackingNo == 1) {
            $course->trackingNo = 2;
            $course->save();
          }
          $status = 'success';
          $message = "CourseOverview Added Successfully";
      } catch (Exception $e) {
          Log::warning('Error Adding CourseOverview',$e->getMessage());
          $status = 'error';
          $message = "Unable to Add CourseOverview";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function getCourseOverview(Request $request, $course_id)
    {
      $CourseOverview = CourseOverview::select('id', 'department_id', 'course_id', 'courseOverview')
                                ->where('course_id', $course_id)->get();
      return response()->json($CourseOverview);
    }

    public function editCourseOverview(Request $request, $CourseOverviewId)
    {
      $status = "";
        $message = "";

        $request->validate([
          'courseOverview' => ['required'],
        ]);
        try {
            if ($CourseOverview = CourseOverview::find($CourseOverviewId)) {
              $CourseOverview->courseOverview = $request->courseOverview;
              $CourseOverview->saved_by       = auth()->user()->id;
              $CourseOverview->save();
            }
            $status = 'success';
            $message = "CourseOverview Updated Successfully";
        } catch (Exception $e) {
            Log::warning('Error Updating CourseOverview', $e->getMessage());
            $status = 'error';
            $message = "Unable to Update CourseOverview";

        }
        return response()->json(['status' => $status, 'message' => $message]);
    }


    public function addCourseObjective(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
          'department_id' => ['required'],
          'course_id' => ['required'],
          'courseObjective' => ['required'],
      ]);
      try {
          CourseObjective::create([
                'id' => Str::uuid(),
                'department_id' => $request->department_id,
                'course_id' => $request->course_id,
                'courseObjectives' => $request->courseObjective,
                'saved_by' => auth()->user()->id,
          ]);
          $course = course_code::find($request->course_id);
          if ($course->trackingNo == 2) {
            $course->trackingNo = 3;
            $course->save();
          }
          $status = 'success';
          $message = "CourseObjective Added Successfully";
      } catch (Exception $e) {
          Log::warning('Error Adding CourseObjective',$e->getMessage());
          $status = 'error';
          $message = "Unable to Add CourseObjective";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function getCourseObjective(Request $request, $course_id)
    {
      $CourseObjective = CourseObjective::select('id', 'department_id', 'course_id', 'courseObjectives')
                                ->where('course_id', $course_id)->get();
      return response()->json($CourseObjective);
    }

    public function editCourseObjective(Request $request, $CourseObjectiveId)
    {
      $status = "";
        $message = "";

        $request->validate([
          'courseObjective' => ['required'],
        ]);
        try {
            if ($CourseObjective = CourseObjective::find($CourseObjectiveId)) {
              $CourseObjective->courseObjectives = $request->courseObjective;
              $CourseObjective->saved_by = auth()->user()->id;
              $CourseObjective->save();
            }
            $status = 'success';
            $message = "CourseObjective Updated Successfully";
        } catch (Exception $e) {
            Log::warning('Error Updating CourseObjective', $e->getMessage());
            $status = 'error';
            $message = "Unable to Update CourseObjective";

        }
        return response()->json(['status' => $status, 'message' => $message]);
    }

    public function addCoursePrerequisite(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
          'department_id' => ['required'],
          'course_id' => ['required'],
          'coursePrerequisite' => ['required'],
      ]);
      try {
          Prerequisite::create([
                'id' => Str::uuid(),
                'department_id' => $request->department_id,
                'course_id' => $request->course_id,
                'coursePrerequisites' => $request->coursePrerequisite,
                'saved_by' => auth()->user()->id,
          ]);
          $course = course_code::find($request->course_id);
          if ($course->trackingNo == 3) {
            $course->trackingNo = 4;
            $course->save();
          }
          $status = 'success';
          $message = "CoursePrerequisite Added Successfully";
      } catch (Exception $e) {
          Log::warning('Error Adding CoursePrerequisite',$e->getMessage());
          $status = 'error';
          $message = "Unable to Add CoursePrerequisite";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function getCoursePrerequisite(Request $request, $course_id)
    {
      $CoursePrerequisite = Prerequisite::select('id', 'department_id', 'course_id', 'coursePrerequisites')
                                ->where('course_id', $course_id)->get();
      return response()->json($CoursePrerequisite);
    }

    public function editCoursePrerequisite(Request $request, $CoursePrerequisiteId)
    {
      $status = "";
        $message = "";

        $request->validate([
          'coursePrerequisite' => ['required'],
        ]);
        try {
            if ($CoursePrerequisite = Prerequisite::find($CoursePrerequisiteId)) {
              $CoursePrerequisite->coursePrerequisites = $request->coursePrerequisite;
              $CoursePrerequisite->saved_by         = auth()->user()->id;
              $CoursePrerequisite->save();
            }
            $status = 'success';
            $message = "CoursePrerequisite Updated Successfully";
        } catch (Exception $e) {
            Log::warning('Error Updating CoursePrerequisite', $e->getMessage());
            $status = 'error';
            $message = "Unable to Update CoursePrerequisite";

        }
        return response()->json(['status' => $status, 'message' => $message]);
    }

    public function addCo(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
          'course_id' => ['required'],
          'cos' => ['required'],
      ]);
      try {

          $labelNumber = 0;
          if ($isAnItem = Co::where('course_id', $request->course_id)->count()) {
            $labelNumber = $isAnItem;
          } 
          $coLabel = 'CO - ';

          for($i = 0; $i < sizeof($request->cos);$i++){
            $labelNumber = $labelNumber + 1;
            Co::create([
                'id' => Str::uuid(),
                'department_id' => auth()->user()->department_id,
                'course_id' => $request['course_id'],
                'labelNo' => $coLabel . ($labelNumber),
                'description' => $request->cos[$i]['co'],
                'cogLevel' => $request->cos[$i]['cogLevel'],
                'saved_by' => auth()->user()->id,
            ]);
          }
          $course = course_code::find($request->course_id);
          if ($course->trackingNo == 8) {
            $course->trackingNo = 9;
            $course->save();
          }
          $status = 'success';
          $message = "CO Added Successfully";
      } catch (Exception $e) {
          Log::warning('Error Adding CO',$e->getMessage());
          $status = 'error';
          $message = "Unable to Add CO";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function getCo($course_id)
    {
      $cos = Co::where('course_id',$course_id)->orderBy('labelNo', 'asc')->get();
      $coCount = Co::where('course_id',$course_id)->count();
      return response()->json(['co'=>$cos, 'coCount'=>$coCount]);
    }

    public function editCo(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
        'cos' => ['required'],
      ]);
      try {
          for($i = 0; $i < sizeof($request->cos);$i++){
            if($cos = Co::find($request->cos[$i]['id']))
            {
                $cos->description = $request->cos[$i]['co'];
                $cos->cogLevel = $request->cos[$i]['cogLevel'];
                $cos->save();
            }
          }
          $status = 'success';
          $message = "CO Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating CO',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update CO";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function deleteCo(Request $request, $coId, $labelNo)
    {
      $status="";
      $message="";

      try {
          if ($cos = Co::find($coId)) {

            $CoPeoMapping = DB::select('SELECT mapping FROM copeos WHERE course_id = :id', ['id' => $cos->course_id]);
            $CoPsoMapping = DB::select('SELECT mapping FROM copsos WHERE course_id = :id', ['id' => $cos->course_id]);
            $CoPoMapping = DB::select('SELECT mapping FROM pocos WHERE course_id = :id', ['id' => $cos->course_id]);

            if ($CoPeoMapping != null) {
              $copeoMappingId = DB::select('SELECT id FROM copeos WHERE course_id = :id', ['id' => $cos->course_id]);
              $copeoMapKey = array();
              $CoPeomapping = $CoPeoMapping[0];
              foreach ($CoPeomapping as $key => $value) {
                $copeoMapKey =  $value;
              }
              $copeoMapKey = json_decode($copeoMapKey, true);
              for ($i = 0; $i < sizeof($copeoMapKey); $i++) {
                $copeoMapKeyValues[$i] = $copeoMapKey[$i];
              }
              unset($copeoMapKeyValues[$labelNo]);
              if($copeo = Copeo::find($copeoMappingId[0]->id))
              {
                  $copeo->mapping = array_values($copeoMapKeyValues);
                  $copeo->save();
              }
            }

            if ($CoPoMapping != null) {
              $copoMappingId = DB::select('SELECT id FROM pocos WHERE course_id = :id', ['id' => $cos->course_id]);
              $copoMapKey = array();
              $CoPomapping = $CoPoMapping[0];
              foreach ($CoPomapping as $key => $value) {
                $copoMapKey =  $value;
              }
              $copoMapKey = json_decode($copoMapKey, true);
              for ($i = 0; $i < sizeof($copoMapKey); $i++) {
                $copoMapKeyValues[$i] = $copoMapKey[$i];
              }
              unset($copoMapKeyValues[$labelNo]);
              if($copo = Poco::find($copoMappingId[0]->id))
              {
                  $copo->mapping = array_values($copoMapKeyValues);
                  $copo->save();
              }
            }

            if ($CoPsoMapping != null) {
              $copsoMappingId = DB::select('SELECT id FROM copsos WHERE course_id = :id', ['id' => $cos->course_id]);
              // $coCount = Co::where('course_id', $cos->course_id)->count();
              $copsoMapKey = array();
              $CoPsomapping = $CoPsoMapping[0];
              foreach ($CoPsomapping as $key => $value) {
                $copsoMapKey =  $value;
              }
              $copsoMapKey = json_decode($copsoMapKey, true);
              for ($i = 0; $i < sizeof($copsoMapKey); $i++) {
                $copsoMapKeyValues[$i] = $copsoMapKey[$i];
              }
              unset($copsoMapKeyValues[$labelNo]);
              if($copso = Copso::find($copsoMappingId[0]->id))
              {
                  $copso->mapping = array_values($copsoMapKeyValues);
                  $copso->save();
              }
            }

            $cos->delete();
          }
          $status = 'success';
          $message = "CO Deleted Successfully";
      } catch (Exception $e) {
          Log::warning('Error Deleting CO',$e->getMessage());
          $status = 'error';
          $message = "Unable to Delete CO";
      }
      return response()->json(['message' => $message, 'status'=>$status]);
    }


    public function addUnits(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
          'department_id' => ['required'],
          'course_id' => ['required'],
          'units' => ['required'],
      ]);
      try {
          $unitNumber = 0;
          if ($isAnItem = AddUnit::where('course_id', $request->course_id)->count()) {
            $unitNumber = $isAnItem;
          }
          for($i = 0; $i < sizeof($request->units);$i++){
            $unitNumber = $unitNumber + 1;
            AddUnit::create([
                'id' => Str::uuid(),
                'department_id' => $request['department_id'],
                'course_id' => $request['course_id'],
                'units' => $unitNumber,
                'content' => $request->units[$i]['unit'],
                'hours' => $request->units[$i]['hours'],
                'cos' => $request->units[$i]['cos'],
                'cogLevel' => $request->units[$i]['cognitive'],
                'saved_by' => auth()->user()->id,
            ]);
          }
          $course = course_code::find($request->course_id);
          if ($course->trackingNo == 4) {
            $course->trackingNo = 5;
            $course->save();
          }
          $status = 'success';
          $message = "Units Added Successfully";
      } catch (Exception $e) {
          Log::warning('Error Adding Units',$e->getMessage());
          $status = 'error';
          $message = "Unable to Add Units";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function getUnits($course_id)
    {
      $units = AddUnit::where('course_id',$course_id)->orderBy('units', 'asc')->get();
      $unitsCount = AddUnit::where('course_id',$course_id)->count();
      return response()->json(['units'=>$units, 'unitsCount'=>$unitsCount]);
      // return($units);
    }

    public function editUnits(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
          'units' => ['required'],
      ]);
      try {
          for($i = 0; $i < sizeof($request->units);$i++){
            if($units = AddUnit::find($request->units[$i]['id']))
            {
                $units->content = $request->units[$i]['unit'];
                $units->hours = $request->units[$i]['hours'];
                $units->cos = $request->units[$i]['cos'];
                $units->cogLevel = $request->units[$i]['cognitive'];
                $units->save();
            }
          }
          $status = 'success';
          $message = "Unit Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating Unit',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update Unit";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function deleteUnits(Request $request, $unitId)
    {
      $status="";
      $message="";
      try {
          if ($units = AddUnit::find($unitId)) {
            $units->delete();
          }
          $status = 'success';
          $message = "Unit Deleted Successfully";
      } catch (Exception $e) {
          Log::warning('Error Deleting Unit',$e->getMessage());
          $status = 'error';
          $message = "Unable to Delete Unit";
      }
      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function addCourseTextBooks(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
          'department_id' => ['required'],
          'course_id' => ['required'],
          'TextBooks' => ['required'],
      ]);
      try {
          TextBook::create([
                'id' => Str::uuid(),
                'department_id' => $request->department_id,
                'course_id' => $request->course_id,
                'TextBooks' => $request->TextBooks,
                'saved_by' => auth()->user()->id,
          ]);
          $course = course_code::find($request->course_id);
          if ($course->trackingNo == 5) {
            $course->trackingNo = 6;
            $course->save();
          }
          $status = 'success';
          $message = "TextBooks Added Successfully";
      } catch (Exception $e) {
          Log::warning('Error Adding TextBooks',$e->getMessage());
          $status = 'error';
          $message = "Unable to Add TextBooks";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function getCourseTextBooks(Request $request, $course_id)
    {
      $CourseTextBooks = TextBook::select('id', 'department_id', 'course_id', 'TextBooks')
                                ->where('course_id', $course_id)->get();
      return response()->json($CourseTextBooks);
    }

    public function editCourseTextBooks(Request $request, $CourseTextBooksId)
    {
      $status = "";
        $message = "";

        $request->validate([
          'TextBooks' => ['required'],
        ]);
        try {
            if ($CourseTextBooks = TextBook::find($CourseTextBooksId)) {
              $CourseTextBooks->TextBooks = $request->TextBooks;
              $CourseTextBooks->saved_by  = auth()->user()->id;
              $CourseTextBooks->save();
            }
            $status = 'success';
            $message = "TextBooks Updated Successfully";
        } catch (Exception $e) {
            Log::warning('Error Updating TextBooks', $e->getMessage());
            $status = 'error';
            $message = "Unable to Update TextBooks";
        }
        return response()->json(['status' => $status, 'message' => $message]);
    }

    public function addCourseReferenceBooks(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
          'department_id' => ['required'],
          'course_id' => ['required'],
          'suggestedReadings' => ['required'],
      ]);
      try {
          ReferenceBook::create([
                'id' => Str::uuid(),
                'department_id' => $request->department_id,
                'course_id' => $request->course_id,
                'ReferenceBooks' => $request->suggestedReadings,
                'saved_by' => auth()->user()->id,
          ]);
          $course = course_code::find($request->course_id);
          if ($course->trackingNo == 6) {
            $course->trackingNo = 7;
            $course->save();
          }
          $status = 'success';
          $message = "ReferenceBooks Added Successfully";
      } catch (Exception $e) {
          Log::warning('Error Adding ReferenceBooks',$e->getMessage());
          $status = 'error';
          $message = "Unable to Add ReferenceBooks";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function getCourseReferenceBooks(Request $request, $course_id)
    {
      $CourseReferenceBooks = ReferenceBook::select('id', 'department_id', 'course_id', 'ReferenceBooks')
                                ->where('course_id', $course_id)->get();
      return response()->json($CourseReferenceBooks);
    }

    public function editCourseReferenceBooks(Request $request, $CourseReferenceBooksId)
    {
      $status = "";
        $message = "";

        $request->validate([
          'suggestedReadings' => ['required'],
        ]);
        try {
            if ($CourseReferenceBooks = ReferenceBook::find($CourseReferenceBooksId)) {
              $CourseReferenceBooks->ReferenceBooks = $request->suggestedReadings;
              $CourseReferenceBooks->saved_by  = auth()->user()->id;
              $CourseReferenceBooks->save();
            }
            $status = 'success';
            $message = "ReferenceBooks Updated Successfully";
        } catch (Exception $e) {
            Log::warning('Error Updating ReferenceBooks', $e->getMessage());
            $status = 'error';
            $message = "Unable to Update ReferenceBooks";

        }
        return response()->json(['status' => $status, 'message' => $message]);
    }

    public function addCourseWebReferences(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
          'department_id' => ['required'],
          'course_id' => ['required'],
          'WebReferences' => ['required'],
      ]);
      try {
          WebReference::create([
                'id' => Str::uuid(),
                'department_id' => $request->department_id,
                'course_id' => $request->course_id,
                'WebReferences' => $request->WebReferences,
                'saved_by' => auth()->user()->id,
          ]);
          $course = course_code::find($request->course_id);
          if ($course->trackingNo == 7) {
            $course->trackingNo = 8;
            $course->save();
          }
          $status = 'success';
          $message = "WebReferences Added Successfully";
      } catch (Exception $e) {
          Log::warning('Error Adding WebReferences',$e->getMessage());
          $status = 'error';
          $message = "Unable to Add WebReferences";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function getCourseWebReferences(Request $request, $course_id)
    {
      $CourseWebReference = WebReference::select('id', 'department_id', 'course_id', 'WebReferences')
                                ->where('course_id', $course_id)->get();
      return response()->json($CourseWebReference);
    }

    public function editCourseWebReferences(Request $request, $CourseWebReferenceId)
    {
      $status = "";
      $message = "";

      $request->validate([
        'WebReferences' => ['required'],
      ]);
      try {
          if ($CourseWebReference = WebReference::find($CourseWebReferenceId)) {
            $CourseWebReference->WebReferences = $request->WebReferences;
            $CourseWebReference->saved_by  = auth()->user()->id;
            $CourseWebReference->save();
          }
          $status = 'success';
          $message = "WebReferences Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating WebReferences', $e->getMessage());
          $status = 'error';
          $message = "Unable to Update WebReferences";

      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function curriculamSummary(Request $request,$id,$programId,$schoolId)
    {
        $cc = new CurriculumController();
        $mm = new MappingController();
        return response()->json(['courseOverview' => $cc->getCourseOverview($request,$id),
        'courseDetails' => $cc->getCourseById($request,$id),
        'courseObjective' => $cc->getCourseObjective($request,$id),
        'coursePrerequisites' => $cc->getCoursePrerequisite($request,$id),
        'units' => $cc->getUnits($id),
        'textBooks' => $cc->getCourseTextBooks($request,$id),
        'suggestedReadings' => $cc->getCourseReferenceBooks($request,$id),
        'webReferences'=> $cc->getCourseWebReferences($request,$id),
        'co' => $cc->getCo($id),
        'coPso' => $mm->getCoPsoMapping($request,$id,$programId),
        'coPeo' => $mm->getPeoCoMapping($request,$id,$schoolId)
        ]);
    }

    public function createDynamicLessonPlan(Request $request)
    {
      $message = "";
      $status = "";

      $request->validate([
        'department_id' => ['required'],
        'course_id' => ['required'],
      ]);

      try {
        $unitNumber = 1;
        for($i = 0; $i < sizeof($request->unit1);$i++){
            dynamicLessonPlan::create([
              'id' => Str::uuid(),
              'department_id' => $request->department_id,
              'course_id' => $request->course_id,
              'unit' => $unitNumber,
              'content' => $request->unit1[$i]['content'],
              'teachingHours' => $request->unit1[$i]['teachingHours'],
              'cognitiveLevel' => $request->unit1[$i]['cognitive'],
              'cos' => $request->unit1[$i]['cos'],
              'coAttainmentThreshold' => $request->unit1[$i]['coattainment'],
              'instructionalMethodologies' => $request->unit1[$i]['instructions'],
              'directAssessmentMethods' => $request->unit1[$i]['damethods'],
              'saved_by' => auth()->user()->id,
          ]);
        }
        $unitNumber += 1;
        for($i = 0; $i < sizeof($request->unit2);$i++){
          dynamicLessonPlan::create([
              'id' => Str::uuid(),
              'department_id' => $request->department_id,
              'course_id' => $request->course_id,
              'unit' => $unitNumber,
              'content' => $request->unit2[$i]['content'],
              'teachingHours' => $request->unit2[$i]['teachingHours'],
              'cognitiveLevel' => $request->unit2[$i]['cognitive'],
              'cos' => $request->unit2[$i]['cos'],
              'coAttainmentThreshold' => $request->unit2[$i]['coattainment'],
              'instructionalMethodologies' => $request->unit2[$i]['instructions'],
              'directAssessmentMethods' => $request->unit2[$i]['damethods'],
              'saved_by' => auth()->user()->id,
          ]);
        }
        $unitNumber += 1;
        for($i = 0; $i < sizeof($request->unit3);$i++){
          dynamicLessonPlan::create([
              'id' => Str::uuid(),
              'department_id' => $request->department_id,
              'course_id' => $request->course_id,
              'unit' => $unitNumber,
              'content' => $request->unit3[$i]['content'],
              'teachingHours' => $request->unit3[$i]['teachingHours'],
              'cognitiveLevel' => $request->unit3[$i]['cognitive'],
              'cos' => $request->unit3[$i]['cos'],
              'coAttainmentThreshold' => $request->unit3[$i]['coattainment'],
              'instructionalMethodologies' => $request->unit3[$i]['instructions'],
              'directAssessmentMethods' => $request->unit3[$i]['damethods'],
              'saved_by' => auth()->user()->id,
          ]);
        }
        $unitNumber += 1;
        for($i = 0; $i < sizeof($request->unit4);$i++){
          dynamicLessonPlan::create([
              'id' => Str::uuid(),
              'department_id' => $request->department_id,
              'course_id' => $request->course_id,
              'unit' => $unitNumber,
              'content' => $request->unit4[$i]['content'],
              'teachingHours' => $request->unit4[$i]['teachingHours'],
              'cognitiveLevel' => $request->unit4[$i]['cognitive'],
              'cos' => $request->unit4[$i]['cos'],
              'coAttainmentThreshold' => $request->unit4[$i]['coattainment'],
              'instructionalMethodologies' => $request->unit4[$i]['instructions'],
              'directAssessmentMethods' => $request->unit4[$i]['damethods'],
              'saved_by' => auth()->user()->id,
          ]);
        }
        $unitNumber += 1;
        for($i = 0; $i < sizeof($request->unit5);$i++){
          dynamicLessonPlan::create([
              'id' => Str::uuid(),
              'department_id' => $request->department_id,
              'course_id' => $request->course_id,
              'unit' => $unitNumber,
              'content' => $request->unit5[$i]['content'],
              'teachingHours' => $request->unit5[$i]['teachingHours'],
              'cognitiveLevel' => $request->unit5[$i]['cognitive'],
              'cos' => $request->unit5[$i]['cos'],
              'coAttainmentThreshold' => $request->unit5[$i]['coattainment'],
              'instructionalMethodologies' => $request->unit5[$i]['instructions'],
              'directAssessmentMethods' => $request->unit5[$i]['damethods'],
              'saved_by' => auth()->user()->id,
          ]);
        }
        $status = 'success';
        $message = "DynamicLessonPlan Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding DynamicLessonPlan', $e->getMessage());
        $status = 'error';
        $message = "Unable to Add DynamicLessonPlan";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }



    public function getDynamicLessonPlan($course_id)
    {
      $matching_1 = ['course_id'=>$course_id, 'unit'=>1];
      $unit_1 = dynamicLessonPlan::where($matching_1)->get();

      $matching_2 = ['course_id'=>$course_id, 'unit'=>2];
      $unit_2 = dynamicLessonPlan::where($matching_2)->get();

      $matching_3 = ['course_id'=>$course_id, 'unit'=>3];
      $unit_3 = dynamicLessonPlan::where($matching_3)->get();

      $matching_4 = ['course_id'=>$course_id, 'unit'=>4];
      $unit_4 = dynamicLessonPlan::where($matching_4)->get();

      $matching_5 = ['course_id'=>$course_id, 'unit'=>5];
      $unit_5 = dynamicLessonPlan::where($matching_5)->get();

      return response()->json(['unit1'=>$unit_1, 'unit2'=>$unit_2, 'unit3'=>$unit_3, 'unit4'=>$unit_4, 'unit5'=>$unit_5]);
    }
    public function updateDynamicLessonPlan(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'unit1' => ['required'],
        'unit2' => ['required'],
        'unit3' => ['required'],
        'unit4' => ['required'],
        'unit5' => ['required'],
      ]);
      try {

        for($i = 0; $i < sizeof($request->unit1);$i++){
            if(isset($request->unit1[$i]['id'])){
                if($unit1 = dynamicLessonPlan::find($request->unit1[$i]['id']))
                {
                $unit1['content'] = $request->unit1[$i]['content'];
                $unit1['teachingHours'] = $request->unit1[$i]['teachingHours'];
                $unit1['cognitiveLevel'] = $request->unit1[$i]['cognitive'];
                $unit1['cos'] = $request->unit1[$i]['cos'];
                $unit1['coAttainmentThreshold'] = $request->unit1[$i]['coattainment'];
                $unit1['instructionalMethodologies'] = $request->unit1[$i]['instructions'];
                $unit1['directAssessmentMethods'] = $request->unit1[$i]['damethods'];
                $unit1->save();
                }
            }
            if(!isset($request->unit1[$i]['id'])){
                $firstUnit = 1;
                dynamicLessonPlan::create([
                    'id' => Str::uuid(),
                    'department_id' => $request->department_id,
                    'course_id' => $request->course_id,
                    'unit' => $firstUnit,
                    'content' => $request->unit1[$i]['content'],
                    'teachingHours' => $request->unit1[$i]['teachingHours'],
                    'cognitiveLevel' => $request->unit1[$i]['cognitive'],
                    'cos' => $request->unit1[$i]['cos'],
                    'coAttainmentThreshold' => $request->unit1[$i]['coattainment'],
                    'instructionalMethodologies' => $request->unit1[$i]['instructions'],
                    'directAssessmentMethods' => $request->unit1[$i]['damethods'],
                    'saved_by' => auth()->user()->id,
                ]);
            }
          }
        for($i = 0; $i < sizeof($request->unit2);$i++){
            if(isset($request->unit2[$i]['id'])){
                if($unit2 = dynamicLessonPlan::find($request->unit2[$i]['id']))
                {
                    $unit2['content'] = $request->unit2[$i]['content'];
                    $unit2['teachingHours'] = $request->unit2[$i]['teachingHours'];
                    $unit2['cognitiveLevel'] = $request->unit2[$i]['cognitive'];
                    $unit2['cos'] = $request->unit2[$i]['cos'];
                    $unit2['coAttainmentThreshold'] = $request->unit2[$i]['coattainment'];
                    $unit2['instructionalMethodologies'] = $request->unit2[$i]['instructions'];
                    $unit2['directAssessmentMethods'] = $request->unit2[$i]['damethods'];
                    $unit2->save();
                }
            }
            if(!isset($request->unit2[$i]['id'])){
                $secondUnit = 2;
                dynamicLessonPlan::create([
                    'id' => Str::uuid(),
                    'department_id' => $request->department_id,
                    'course_id' => $request->course_id,
                    'unit' => $secondUnit,
                    'content' => $request->unit2[$i]['content'],
                    'teachingHours' => $request->unit2[$i]['teachingHours'],
                    'cognitiveLevel' => $request->unit2[$i]['cognitive'],
                    'cos' => $request->unit2[$i]['cos'],
                    'coAttainmentThreshold' => $request->unit2[$i]['coattainment'],
                    'instructionalMethodologies' => $request->unit2[$i]['instructions'],
                    'directAssessmentMethods' => $request->unit2[$i]['damethods'],
                    'saved_by' => auth()->user()->id,
                ]);
            }
        }
        for($i = 0; $i < sizeof($request->unit3);$i++){
            if(isset($request->unit3[$i]['id'])){
                if($unit3 = dynamicLessonPlan::find($request->unit3[$i]['id']))
                {
                    $unit3['content'] = $request->unit3[$i]['content'];
                    $unit3['teachingHours'] = $request->unit3[$i]['teachingHours'];
                    $unit3['cognitiveLevel'] = $request->unit3[$i]['cognitive'];
                    $unit3['cos'] = $request->unit3[$i]['cos'];
                    $unit3['coAttainmentThreshold'] = $request->unit3[$i]['coattainment'];
                    $unit3['instructionalMethodologies'] = $request->unit3[$i]['instructions'];
                    $unit3['directAssessmentMethods'] = $request->unit3[$i]['damethods'];
                    $unit3->save();
                }
            }
            if(!isset($request->unit3[$i]['id'])){
                $thirdUnit = 3;
                dynamicLessonPlan::create([
                    'id' => Str::uuid(),
                    'department_id' => $request->department_id,
                    'course_id' => $request->course_id,
                    'unit' => $thirdUnit,
                    'content' => $request->unit3[$i]['content'],
                    'teachingHours' => $request->unit3[$i]['teachingHours'],
                    'cognitiveLevel' => $request->unit3[$i]['cognitive'],
                    'cos' => $request->unit3[$i]['cos'],
                    'coAttainmentThreshold' => $request->unit3[$i]['coattainment'],
                    'instructionalMethodologies' => $request->unit3[$i]['instructions'],
                    'directAssessmentMethods' => $request->unit3[$i]['damethods'],
                    'saved_by' => auth()->user()->id,
                ]);
            }

        }
        for($i = 0; $i < sizeof($request->unit4);$i++){
            if(isset($request->unit4[$i]['id'])){
                if($unit4 = dynamicLessonPlan::find($request->unit4[$i]['id']))
                {
                    $unit4['content'] = $request->unit4[$i]['content'];
                    $unit4['teachingHours'] = $request->unit4[$i]['teachingHours'];
                    $unit4['cognitiveLevel'] = $request->unit4[$i]['cognitive'];
                    $unit4['cos'] = $request->unit4[$i]['cos'];
                    $unit4['coAttainmentThreshold'] = $request->unit4[$i]['coattainment'];
                    $unit4['instructionalMethodologies'] = $request->unit4[$i]['instructions'];
                    $unit4['directAssessmentMethods'] = $request->unit4[$i]['damethods'];
                    $unit4->save();
                }
            }
            if(!isset($request->unit4[$i]['id'])){
                $fourthUnit = 4;
                dynamicLessonPlan::create([
                    'id' => Str::uuid(),
                    'department_id' => $request->department_id,
                    'course_id' => $request->course_id,
                    'unit' => $fourthUnit,
                    'content' => $request->unit4[$i]['content'],
                    'teachingHours' => $request->unit4[$i]['teachingHours'],
                    'cognitiveLevel' => $request->unit4[$i]['cognitive'],
                    'cos' => $request->unit4[$i]['cos'],
                    'coAttainmentThreshold' => $request->unit4[$i]['coattainment'],
                    'instructionalMethodologies' => $request->unit4[$i]['instructions'],
                    'directAssessmentMethods' => $request->unit4[$i]['damethods'],
                    'saved_by' => auth()->user()->id,
                ]);
            }
        }
        for($i = 0; $i < sizeof($request->unit5);$i++){
            if(isset($request->unit5[$i]['id'])){
                if($unit5 = dynamicLessonPlan::find($request->unit5[$i]['id']))
                {
                    $unit5['content'] = $request->unit5[$i]['content'];
                    $unit5['teachingHours'] = $request->unit5[$i]['teachingHours'];
                    $unit5['cognitiveLevel'] = $request->unit5[$i]['cognitive'];
                    $unit5['cos'] = $request->unit5[$i]['cos'];
                    $unit5['coAttainmentThreshold'] = $request->unit5[$i]['coattainment'];
                    $unit5['instructionalMethodologies'] = $request->unit5[$i]['instructions'];
                    $unit5['directAssessmentMethods'] = $request->unit5[$i]['damethods'];
                    $unit5->save();
                }
            }
            if(!isset($request->unit5[$i]['id'])){
                $fifthUnit = 5;
                dynamicLessonPlan::create([
                    'id' => Str::uuid(),
                    'department_id' => $request->department_id,
                    'course_id' => $request->course_id,
                    'unit' => $fifthUnit,
                    'content' => $request->unit5[$i]['content'],
                    'teachingHours' => $request->unit5[$i]['teachingHours'],
                    'cognitiveLevel' => $request->unit5[$i]['cognitive'],
                    'cos' => $request->unit5[$i]['cos'],
                    'coAttainmentThreshold' => $request->unit5[$i]['coattainment'],
                    'instructionalMethodologies' => $request->unit5[$i]['instructions'],
                    'directAssessmentMethods' => $request->unit5[$i]['damethods'],
                    'saved_by' => auth()->user()->id,
                ]);
            }

        }

        $status = 'success';
        $message = "DynamicLessonPlan Successfully Updated";
      } catch (Exception $e) {
        Log::warning('Error Updating DynamicLessonPlan', $e->getMessage());
        $status = 'error';
        $message = "Unable to Update DynamicLessonPlan";
      }

      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function deleteDynamicLessonPlan($dynamicLessonPlanID)
    {
      $status="";
      $message="";
      try {
          if ($units = dynamicLessonPlan::find($dynamicLessonPlanID)) {
            $units->delete();
          }
          $status = 'success';
          $message = "Deleted Successfully";
      } catch (Exception $e) {
          Log::warning('Error Deleting',$e->getMessage());
          $status = 'error';
          $message = "Unable to Delete";
      }
      return response()->json(['status' => $status,'message'=>$message]);
    }

}
