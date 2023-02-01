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
use App\Models\Studentmark;
use App\Models\Department;
use App\Models\Indirectpso;
use App\Models\program;
use App\Models\school;
use App\Models\Peopo;
use App\Models\Peo;
use App\Models\Po;

class AttainmentController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(['auth', 'hod']);
    }

    public function getPrograms()
    {
      $department = Department::find(auth()->user()->department_id);
      $programs = Studentmark::selectRaw('distinct program_name')
                  ->where('department_name', $department["department_name"])->get();

      return response()->json($programs);
    }

    public function getCourses(Request $request)
    {
        $department = Department::find(auth()->user()->department_id);
        $coConsolidated = array();
        $courses = DB::select(
            'SELECT 
                consolidated_co 
            FROM 
                studentmarks 
            WHERE 
                (program_name = :program) AND 
                (department_name = :department) AND 
                (regulation = :academicYEAR) 
            ORDER BY 
                co_avarage 
            DESC
            ', [
                'program' => $request->program_name, 
                'department' => $department['department_name'], 
                'academicYEAR' => $request->regulation
              ]
         );
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

    public function getUserSchool()
    {
        $department = Department::find(auth()->user()->department_id);
        $school = school::find($department->school_id);
        return response()->json($school);
    }

    public function addPeoPoMapping(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'school_id' => ['required'],
        'peopos' => ['required'],
      ]);

      try {
        Peopo::create([
              'id' => Str::uuid(),
              'school_id' => $request->school_id,
              'mapping' => json_encode($request->peopos),
              'saved_by' => auth()->user()->id,
        ]);
        $status = 'success';
        $message = "PEO-PO Mapping Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding PEO-PO Mapping',$e->getMessage());
        $status = 'error';
        $message = "Unable to Add PEO-PO Mapping";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function getPeoPoMapping(Request $request, $school_id)
    {
      $popeoMapppingChart = array();
      $MapKey = array();
      $MapKeyValues = array();
      // $NoOfPeos = Peo::where('school_id', $school_id)->count();
      // $NoOfPos = Po::where('school_id', $school_id)->count();
      $NoOfPeos = 6;
      $NoOfPos = 7;
      $PeoPoMapping = DB::select('SELECT mapping FROM peopos WHERE school_id = :id', ['id' => $school_id]);
      $PeoPoMappingId = DB::select('SELECT id FROM peopos WHERE school_id = :id', ['id' => $school_id]);

      // justification stuff
      $MapKeys = array();
      $MapKeysValues = array();
      $JustificationKeyValues = array();
      $PeoPoMappingJustification = DB::select('SELECT mappingJustification FROM justificationpeopos WHERE school_id = :id', ['id' => $school_id]);
      $PeoPoMappingJustificationId = DB::select('SELECT id FROM justificationpeopos WHERE school_id = :id', ['id' => $school_id]);


      if ($PeoPoMapping == null) {
        $PeoPoMapping = array('');
        return response()->json(['mapping'=>$MapKeyValues, 'justication'=>$MapKeysValues, 'peoCount'=>$NoOfPeos, 'poCount'=>$NoOfPos]);
      }
      $mapping = $PeoPoMapping[0];

      foreach ($mapping as $key => $value) {
        $MapKey =  $value;
      }
      $MapKey = json_decode($MapKey, true);
      for ($i = 0; $i < sizeof($MapKey); $i++) {
        $MapKeyValues[$i] = $MapKey[$i];
      }
      $labelData = "peopo";
      $labelChart = array("PO1", "PO2", "PO3", "PO4", "PO5", "PO6", "PO7", "PO8", "PO9", "PO10", "PO11", "PO12", "PO13", "PO14", "PO15");
      // $labelChart = array("PEO1", "PEO2", "PEO3", "PEO4", "PEO5", "PEO6", "PEO7", "PEO8", "PEO9", "PEO10", "PEO11", "PEO12", "PEO13", "PEO14", "PEO15");

      $mappingChart = array();

      // correlation
      $highCorrelation = array();
      $moderateCorrelation = array();
      $lowCorrelation = array();

      $SlightCorrelation = 0;
      $ModerateCorrelation = 0;
      $HighCorrelation = 0;

      if (isset($MapKeyValues[$NoOfPeos-1])) {
        for ($i=0; $i<$NoOfPeos; $i++) {
          // array_push($mappingChart,$labelChart[$i]);
          for ($j=1; $j<=$NoOfPos; $j++) {
            $mappingChart[] = $MapKeyValues[$i][$labelData.$j];
          }
        }
        $requiredPOlabels = array();
        for ($i=0; $i < $NoOfPos; $i++) {
          $requiredPOlabels[] = $labelChart[$i];
        }
        $peopoMappingChart = array_chunk($mappingChart, $NoOfPos);

        array_unshift($peopoMappingChart, $requiredPOlabels);
        for ($i=0; $i < $NoOfPos; $i++) {
          $popeoMapppingChart[] = array_column($peopoMappingChart, $i);
        }
        $popeoMapppingChart = collect($popeoMapppingChart)->filter();


        $checkEmptyPo = 0;
        for ($i=0; $i < sizeof($mappingChart); $i++) {
            if (null == $mappingChart[$i]) {
              $checkEmptyPo = 1;
            }
        }

        if ($checkEmptyPo == 0) {
          // Counting Correlation
          $correlation = array_count_values($mappingChart);

            if (array_key_exists(1,$correlation)) {
              $SlightCorrelation = round(((($correlation[1])/($NoOfPeos*$NoOfPos))*100), 2);
            }
            if (array_key_exists(2,$correlation)) {
              $ModerateCorrelation = round(((($correlation[2])/($NoOfPeos*$NoOfPos))*100), 2);
            }
            if (array_key_exists(3,$correlation)) {
              $HighCorrelation = round(((($correlation[3])/($NoOfPeos*$NoOfPos))*100), 2);
            }
        }


        // getRoute data for justification
        $mappingPeoPoJustification = array();
        $mappingPeoPoJustification = array_chunk($mappingChart, $NoOfPos);
        $high = array();
        $moderate = array();
        $low = array();
        $row = array();

        $rowsInMapping = $NoOfPeos; // Also equals to "sizeof($mappingPeoPoJustification)"

        for ($noOfRows=0; $noOfRows < $NoOfPeos; $noOfRows++) {
          array_push($high, $row);
          array_push($moderate, $row);
          array_push($low, $row);
        }

        $peoLabel = "PEO-";
        for ($i = 0, $rowNo = 0;$i < $NoOfPeos,$rowNo < $NoOfPeos;$i++, $rowNo++) {
          for ($j=0; $j < $NoOfPos; $j++) {
            if (1 == $mappingPeoPoJustification[$i][$j]) {
              array_push($low[$rowNo], $j+1);
            }
            elseif (2 == $mappingPeoPoJustification[$i][$j]) {
              array_push($moderate[$rowNo], $j+1);
            }
            else {
              array_push($high[$rowNo], $j+1);
            }
          }
        }


        for ($i = 0;$i < $NoOfPeos;$i++) {
          array_push($highCorrelation, preg_filter('/^/', 'PO', $high[$i]));
        }
        for ($i = 0;$i < $NoOfPeos;$i++) {
          array_push($moderateCorrelation, preg_filter('/^/', 'PO', $moderate[$i]));
        }
        for ($i = 0;$i < $NoOfPeos;$i++) {
          array_push($lowCorrelation, preg_filter('/^/', 'PO', $low[$i]));
        }

        for ($i=0; $i < $NoOfPeos; $i++) {
          array_unshift($highCorrelation[$i], $peoLabel.($i+1));
          array_unshift($moderateCorrelation[$i], $peoLabel.($i+1));
          array_unshift($lowCorrelation[$i], $peoLabel.($i+1));
        }

        // array string
        for ($i=0; $i < $NoOfPeos; $i++) {
          $highCorrelation[$i] = implode(", ",$highCorrelation[$i]);

          $moderateCorrelation[$i] = implode(", ",$moderateCorrelation[$i]);

          $lowCorrelation[$i] = implode(", ",$lowCorrelation[$i]);
        }

        for ($i=0; $i < $NoOfPeos; $i++) {
          $highCorrelation[$i][5] = ":";
          $moderateCorrelation[$i][5] = ":";
          $lowCorrelation[$i][5] = ":";
        }


        // get route for justificationData

        if ($PeoPoMappingJustification == null) {
          $MapKeysValues = array();
        }
        else {
          $mappingJustification = $PeoPoMappingJustification[0];

          foreach ($mappingJustification as $key => $value) {
            $MapKeys =  $value;
          }
          $MapKeys = json_decode($MapKeys, true);
          for ($i = 0; $i < sizeof($MapKeys); $i++) {
            $MapKeysValues[$i] = $MapKeys[$i];
          }
        }
      }

      return response()->json(['peoCount'=>$NoOfPeos, 'poCount'=>$NoOfPos,'mapping'=>$MapKeyValues,'justification'=>$MapKeysValues,
      'SlightCorrelation'=>$SlightCorrelation, 'ModerateCorrelation'=>$ModerateCorrelation, 'HighCorrelation'=>$HighCorrelation,
      'justificationHigh'=>$highCorrelation, 'justificationModern'=>$moderateCorrelation, 'justificationLow'=>$lowCorrelation,
      'justificationId'=>$PeoPoMappingJustificationId, 'MappingId' => $PeoPoMappingId, 'chart'=>$popeoMapppingChart
    ]);
    }

    public function editPeoPoMapping(Request $request, $peopoMappingId)
    {
      $status="";
      $message="";
      $request->validate([
          'peopos' => 'required',
      ]);
      try {
          if($peopo = Peopo::find($peopoMappingId))
          {
              $peopo->mapping = $request->peopos;
              $peopo->save();
          }

          $status = 'success';
          $message = "PEO-PO Mapping Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating PEO-PO Mapping',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update PEO-PO Mapping";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function getCoPsoReport($program_name, $regulation){
      $department = Department::find(auth()->user()->department_id);
      $coConsolidated = array();
      $courses = DB::select(
                      'SELECT
                          assessment_copsos.copso
                      FROM 
                          studentmarks 
                      INNER JOIN
                          assessment_copsos
                      ON
                          studentmarks.id = assessment_copsos.co_id
                      WHERE 
                          (studentmarks.program_name = :program) 
                            AND 
                          (studentmarks.department_name = :department) 
                            AND
                          (studentmarks.regulation = :academicYear)
                      ORDER BY 
                          assessment_copsos.copso_avarage 
                      DESC', 
                      ['program' => $program_name, 'department' => $department['department_name'], 'academicYear' => $regulation]
                  );
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

    public function getAvgCoPso($regulation)
    {
      $matching = ['departments.id' => auth()->user()->department_id, 'users.id' => auth()->user()->id];
      $department_name = Department::find(auth()->user()->department_id);
      $match = [
        "studentmarks.regulation" => $regulation, 
        "studentmarks.department_name" => $department_name["department_name"]
      ];
      $copso_avarage = Studentmark::join('assessment_copsos', 'assessment_copsos.co_id', '=', 'studentmarks.id')
                       ->selectRaw('studentmarks.program_name, AVG(assessment_copsos.copso_avarage) as avarage')
                       ->groupBy('studentmarks.program_name')
                       ->where($match)->get();
      return response()->json($copso_avarage);
    }

    public function getCoPoReport($school_id, $regulation){
      $coConsolidated = array();
      $courses = DB::select(
                      'SELECT
                        assessment_copos.copo
                      FROM
                        departments
                      INNER JOIN
                        studentmarks ON departments.department_name = studentmarks.department_name
                      INNER JOIN
                        schools ON departments.school_id = schools.id
                      INNER JOIN
                        assessment_copos ON assessment_copos.co_id = studentmarks.id
                      WHERE 
                          (schools.id = :school_id)
                            AND
                          (studentmarks.regulation = :academicYear)
                      ORDER BY 
                          assessment_copos.copo_avarage 
                      DESC', 
                      ['school_id' => $school_id, 'academicYear' => $regulation]
                  );
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

    public function getAvgCoPo($regulation)
    {
      $copo_avarage = Department::join('studentmarks', 'studentmarks.department_name', '=', 'departments.department_name')
                        ->join('schools', 'schools.id', '=', 'departments.school_id')
                        ->join('assessment_copos', 'assessment_copos.co_id', '=', 'studentmarks.id')
                        ->selectRaw('schools.school_name, AVG(assessment_copos.copo_avarage) as avarage')
                        ->groupBy('schools.school_name')
                        ->get();
      return response()->json($copo_avarage);
    }

    public function sendSchoolsCopoReport()
    {
      $schools = Department::join('studentmarks', 'studentmarks.department_name', '=', 'departments.department_name')
                           ->join('schools', 'schools.id', '=', 'departments.school_id')
                           ->join('assessment_copos', 'assessment_copos.co_id', '=', 'studentmarks.id')
                           ->select('schools.id', 'schools.school_name')->get();
      $uniqueSchools = $schools->unique('id');
      return response()->json($uniqueSchools);
    }

    public function getPsoIndirect($program_name, $regulation)
    {
      $matching_for_data = ["course_code" => $program_name, "regulation" => $regulation];
      $PSO_10 = array();
      $PSO_100 = array();
      try{
        $department = Department::find(auth()->user()->department_id);
        $matching_for_psoIndirect = ["dept_name" => $department["department_name"], "programName" => $program_name, "regulations" => $regulation];
        $psoIndirect_100 = DB::select('SELECT indirect_assessment_100_percentage FROM indirectpsos WHERE department_name = :dept_name AND program_name = :programName AND regulation = :regulations', $matching_for_psoIndirect);
        $psoIndirect_10 = DB::select('SELECT indirect_assessment_10_percentage FROM indirectpsos WHERE department_name = :dept_name AND program_name = :programName AND regulation = :regulations', $matching_for_psoIndirect);
       
        if($psoIndirect_100 != null){
          $mapping_indirect_100 = $psoIndirect_100;
          $mapping_indirect_10 = $psoIndirect_10;

          ############ PSO-100%  ###########
          for ($i = 0; $i < sizeof($mapping_indirect_100); $i++) {
            $EACHcourse = $mapping_indirect_100[$i];
            foreach ($EACHcourse as $keys => $values) {
              $PsoJsonKey =  $values;
            }
            $PSO100 = json_decode($PsoJsonKey, true);
            array_push($PSO_100, $PSO100);
          }
          $PSO_100 = collect($PSO_100)->filter();

          ############ PSO-10%  ###########
          for ($i = 0; $i < sizeof($mapping_indirect_10); $i++) {
            $EACHcourse = $mapping_indirect_10[$i];
            foreach ($EACHcourse as $keys => $values) {
              $PsoJsonKey =  $values;
            }
            $PSO10 = json_decode($PsoJsonKey, true);
            array_push($PSO_10, $PSO10);
          }
          $PSO_10 = collect($PSO_10)->filter();
          return response()->json(['indirect_100_percentage'=>$PSO_100, 'indirect_10_percentage'=>$PSO_10]);
        }else{
          return response()->json(['indirect_100_percentage'=>array(), 'indirect_10_percentage'=>array()]);
        }
      }catch(Exception $e){
        return response()->json($e);
      }
    }


    public function getPsoCoAvarageOnDirectAssessment($regulation)
    {
      $department = Department::find(auth()->user()->department_id);
      $matching = ["studentmarks.regulation" => $regulation, "studentmarks.department_name" => $department["department_name"]];
      $copso_avarage = Studentmark::join('assessment_copsos', 'assessment_copsos.co_id', '=', 'studentmarks.id')
      ->selectRaw('
          studentmarks.program_name,
          avg(copso->>"$.PSO1") as PSO1, 
          avg(copso->>"$.PSO2") as PSO2, 
          avg(copso->>"$.PSO3") as PSO3, 
          avg(copso->>"$.PSO4") as PSO4,
          avg(copso->>"$.PSO5") as PSO5, 
          avg(copso->>"$.PSO6") as PSO6, 
          avg(copso->>"$.PSO7") as PSO7
      ')
      ->groupBy('studentmarks.program_name')
      ->where($matching)->get();
      return response()->json($copso_avarage);
    }


    public function getPsoCoInDirectAssessment($regulation)
    {
      $department_data = [];
      $department = Department::find(auth()->user()->department_id);
      
      $data = Indirectpso::where('department_name', $department["department_name"])
                          ->select('program_name', 'indirect_assessment_10_percentage')->get();
      foreach ($data as $val) {
        $program = program::select('id', 'program_name')->where('department_id', $val->id)->get();
        $department_data[] = [
            'id' => $val->id,
            'school_id' => $val->school_id,
            'department_name' => $val->department_name,
            'program' => $program,
        ];
    }
      return response()->json($data);
    }

    
    public function getPoCoAvarageOnDirectAssessment($regulation)
    {
      $department = Department::find(auth()->user()->department_id);
      $matching = ["studentmarks.regulation" => $regulation, "studentmarks.department_name" => $department["department_name"]];
      $copso_avarage = Studentmark::join('assessment_copos', 'assessment_copos.co_id', '=', 'studentmarks.id')
                                    ->join('departments', 'departments.department_name', '=', 'studentmarks.department_name')
                                    ->join('schools', 'schools.id', '=', 'departments.school_id')
                                    ->selectRaw('
                                        schools.school_name,
                                        avg(copo->>"$.PO1") as PO1, 
                                        avg(copo->>"$.PO2") as PO2, 
                                        avg(copo->>"$.PO3") as PO3, 
                                        avg(copo->>"$.PO4") as PO4,
                                        avg(copo->>"$.PO5") as PO5, 
                                        avg(copo->>"$.PO6") as PO6, 
                                        avg(copo->>"$.PO7") as PO7
                                    ')
                                    ->groupBy('schools.school_name')
                                    ->where($matching)->get();
      return response()->json($copso_avarage);
    }

    
}
