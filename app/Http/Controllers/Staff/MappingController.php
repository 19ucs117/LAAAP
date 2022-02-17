<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;

use App\Models\Co;
use App\Models\Peo;
use App\Models\Po;
use App\Models\Pso;
use App\Models\copso;
use App\Models\Poco;
use App\Models\Copeo;
use App\Models\Justificationcopso;
use App\Models\Justificationcopo;
use App\Models\Justificationcopeo;
use App\Models\course_code;


class MappingController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(['auth', 'staff']);
    }

    public function addCoPsoMapping(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'program_id' => ['required'],
        'course_id' => ['required'],
        'copsos' => ['required']
      ]);
      try {
          copso::create([
                'id' => Str::uuid(),
                'program_id' => $request->program_id,
                'course_id' => $request->course_id,
                'mapping' => json_encode($request->copsos),
                'saved_by' => auth()->user()->id,
          ]);
        $status = 'success';
        $message = "CO-PSO Mapping Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding CO-PSO Mapping',$e->getMessage());
        $status = 'error';
        $message = "Unable to Add CO-PSO";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function getCoPsoMapping(Request $request, $course_id, $program_id)
    {
        $psocoMapppingChart = array();
        $MapKey = array();
        $MapKeyValues = array();
        $NoOfCos = Co::where('course_id', $course_id)->count();  //row
        $NoOfPsos = Pso::where('program_id', $program_id)->count();  //column
        $CoPsoMapping = DB::select('SELECT mapping FROM copsos WHERE course_id = :id', ['id' => $course_id]);
        $CoPsoMappingId = DB::select('SELECT id FROM copsos WHERE course_id = :id', ['id' => $course_id]);

        // justification stuff
        $MapKeys = array();
        $MapKeysValues = array();
        $JustificationKeyValues = array();
        $CoPsoMappingJustification = DB::select('SELECT mappingJustification FROM justificationcopsos WHERE course_id = :id', ['id' => $course_id]);
        $CoPsoMappingJustificationId = DB::select('SELECT id FROM justificationcopsos WHERE course_id = :id', ['id' => $course_id]);


        if ($CoPsoMapping == null) {
          $CoPsoMapping = array('');
          return response()->json(['mapping'=>$MapKeyValues, 'justication'=>$MapKeysValues, 'coCount'=>$NoOfCos, 'psoCount'=>$NoOfPsos]);
        }
        $mapping = $CoPsoMapping[0];

        foreach ($mapping as $key => $value) {
          $MapKey =  $value;
        }
        $MapKey = json_decode($MapKey, true);
        for ($i = 0; $i < sizeof($MapKey); $i++) {
          $MapKeyValues[$i] = $MapKey[$i];
        }
        $labelData = "copso";
        $labelChart = array("PSO1", "PSO2", "PSO3", "PSO4", "PSO5", "PSO6", "PSO7", "PSO8", "PSO9", "PSO10", "PSO11", "PSO12", "PSO13", "PSO14", "PSO15");
        // $labelChart = array("PEO1", "PEO2", "PEO3", "PEO4", "PEO5", "PEO6", "PEO7", "PEO8", "PEO9", "PEO10", "PEO11", "PEO12", "PEO13", "PEO14", "PEO15");

        $mappingChart = array();

        // correlation
        $highCorrelation = array();
        $moderateCorrelation = array();
        $lowCorrelation = array();

        $SlightCorrelation = 0;
        $ModerateCorrelation = 0;
        $HighCorrelation = 0;

        if (isset($MapKeyValues[$NoOfCos-1])) {
          for ($i=0; $i<$NoOfCos; $i++) {
            // array_push($mappingChart,$labelChart[$i]);
            for ($j=1; $j<=$NoOfPsos; $j++) {
              $mappingChart[] = $MapKeyValues[$i][$labelData.$j];
            }
          }
          $requiredPSOlabels = array();
          for ($i=0; $i < $NoOfPsos; $i++) {
            $requiredPSOlabels[] = $labelChart[$i];
          }
          $copsoMappingChart = array_chunk($mappingChart, $NoOfPsos);

          array_unshift($copsoMappingChart, $requiredPSOlabels);
          for ($i=0; $i < $NoOfPsos; $i++) {
            $psocoMapppingChart[] = array_column($copsoMappingChart, $i);
          }
          $psocoMapppingChart = collect($psocoMapppingChart)->filter();


          $checkEmptyPso = 0;
          for ($i=0; $i < sizeof($mappingChart); $i++) {
              if (null == $mappingChart[$i]) {
                $checkEmptyPso = 1;
              }
          }

          if ($checkEmptyPso == 0) {
            // Counting Correlation
            $correlation = array_count_values($mappingChart);

              if (array_key_exists(1,$correlation)) {
                $SlightCorrelation = round(((($correlation[1])/($NoOfCos*$NoOfPsos))*100), 2);
              }
              if (array_key_exists(2,$correlation)) {
                $ModerateCorrelation = round(((($correlation[2])/($NoOfCos*$NoOfPsos))*100), 2);
              }
              if (array_key_exists(3,$correlation)) {
                $HighCorrelation = round(((($correlation[3])/($NoOfCos*$NoOfPsos))*100), 2);
              }
          }


          // getRoute data for justification
          $mappingCoPsoJustification = array();
          $mappingCoPsoJustification = array_chunk($mappingChart, $NoOfPsos);
          $high = array();
          $moderate = array();
          $low = array();
          $row = array();

          $rowsInMapping = $NoOfCos; // Also equals to "sizeof($mappingCoPsoJustification)"

          for ($noOfRows=0; $noOfRows < $NoOfCos; $noOfRows++) {
            array_push($high, $row);
            array_push($moderate, $row);
            array_push($low, $row);
          }

          $coLabel = "CO-";
          for ($i = 0, $rowNo = 0;$i < $NoOfCos,$rowNo < $NoOfCos;$i++, $rowNo++) {
            for ($j=0; $j < $NoOfPsos; $j++) {
              if (1 == $mappingCoPsoJustification[$i][$j]) {
                array_push($low[$rowNo], $j+1);
              }
              elseif (2 == $mappingCoPsoJustification[$i][$j]) {
                array_push($moderate[$rowNo], $j+1);
              }
              else {
                array_push($high[$rowNo], $j+1);
              }
            }
          }


          for ($i = 0;$i < $NoOfCos;$i++) {
            array_push($highCorrelation, preg_filter('/^/', 'PSO', $high[$i]));
          }
          for ($i = 0;$i < $NoOfCos;$i++) {
            array_push($moderateCorrelation, preg_filter('/^/', 'PSO', $moderate[$i]));
          }
          for ($i = 0;$i < $NoOfCos;$i++) {
            array_push($lowCorrelation, preg_filter('/^/', 'PSO', $low[$i]));
          }

          for ($i=0; $i < $NoOfCos; $i++) {
            array_unshift($highCorrelation[$i], $coLabel.($i+1));
            array_unshift($moderateCorrelation[$i], $coLabel.($i+1));
            array_unshift($lowCorrelation[$i], $coLabel.($i+1));
          }

          // array string
          for ($i=0; $i < $NoOfCos; $i++) {
            $highCorrelation[$i] = implode(", ",$highCorrelation[$i]);
            $moderateCorrelation[$i] = implode(", ",$moderateCorrelation[$i]);
            $lowCorrelation[$i] = implode(", ",$lowCorrelation[$i]);
          }

          for ($i=0; $i < $NoOfCos; $i++) {
            $highCorrelation[$i][4] = ":";
            $moderateCorrelation[$i][4] = ":";
            $lowCorrelation[$i][4] = ":";
          }


          // get route for justificationData

          if ($CoPsoMappingJustification == null) {
            $MapKeysValues = array();
          }
          else {
            $mappingJustification = $CoPsoMappingJustification[0];

            foreach ($mappingJustification as $key => $value) {
              $MapKeys =  $value;
            }
            $MapKeys = json_decode($MapKeys, true);
            for ($i = 0; $i < sizeof($MapKeys); $i++) {
              $MapKeysValues[$i] = $MapKeys[$i];
            }
          }
        }

        return response()->json(['coCount'=>$NoOfCos, 'psoCount'=>$NoOfPsos,'mapping'=>$MapKeyValues,'justification'=>$MapKeysValues,
        'SlightCorrelation'=>$SlightCorrelation, 'ModerateCorrelation'=>$ModerateCorrelation, 'HighCorrelation'=>$HighCorrelation,
        'justificationHigh'=>$highCorrelation, 'justificationModern'=>$moderateCorrelation, 'justificationLow'=>$lowCorrelation,
        'justificationId'=>$CoPsoMappingJustificationId, 'MappingId' => $CoPsoMappingId, 'chart'=>$psocoMapppingChart
      ]);

    }

    public function editCoPsoMapping(Request $request, $copsoMappingId)
    {
      $status="";
      $message="";
      $request->validate([
          'copsos' => 'required',
      ]);
      try {
          if($psoco = copso::find($copsoMappingId))
          {
              $psoco->mapping = $request->copsos;
              $psoco->save();
          }

          $status = 'success';
          $message = "CO-PSO Mapping Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating CO-PSO Mapping',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update CO-PSO Mapping";

      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function addCoPsoMappingJustification(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'course_id' => ['required'],
        'program_id' => ['required'],
        'justification' => ['required'],
      ]);

      try {
        Justificationcopso::create([
              'id' => Str::uuid(),
              'program_id' => $request->program_id,
              'course_id' => $request->course_id,
              'mappingJustification' => json_encode($request->justification),
              'saved_by' => auth()->user()->id,
        ]);
        $course = course_code::find($request->course_id);
          if ($course->trackingNo == 9) {
            $course->trackingNo = 10;
            $course->save();
          }
        $status = 'success';
        $message = "CO-PSO MappingJustification Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding CO-PSO MappingJustification',$e->getMessage());
        $status = 'error';
        $message = "Unable to add CO-PSO Justification";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function editCoPsoMappingJustification(Request $request, $copsoMappingJustificationId)
    {
      $status="";
      $message="";
      $request->validate([
          'justification' => 'required',
      ]);
      try {
          if($copso = Justificationcopso::find($copsoMappingJustificationId))
          {
              $copso->mappingJustification = $request->justification;
              $copso->save();
          }

          $status = 'success';
          $message = "CO-PSO Mapping Justification Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating Justification CO-PSO Mapping',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update Justification CO-PSO Mapping";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function addPoCoMapping(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'school_id' => ['required'],
        'course_id' => ['required'],
        'pocos' => ['required']
      ]);
      try {
          Poco::create([
                'id' => Str::uuid(),
                'school_id' => $request->school_id,
                'course_id' => $request->course_id,
                'mapping' => json_encode($request->pocos),
                'saved_by' => auth()->user()->id,
          ]);

        $status = 'success';
        $message = "CO-PO Mapping Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding CO-PO Mapping',$e->getMessage());
        $status = 'error';
        $message = "Unable to Add CO-PO";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function getPoCoMapping(Request $request, $course_id, $school_id)
    {
      $pocoMapppingChart = array();
      $MapKey = array();
      $MapKeyValues = array();
      $NoOfCos = Co::where('course_id', $request->course_id)->count(); // row
      $NoOfPos = Po::where('school_id', $school_id)->count();   // column
      $CoPoMapping = DB::select('SELECT mapping FROM pocos WHERE course_id = :id', ['id' => $course_id]);
      $CoPoMappingId = DB::select('SELECT id FROM pocos WHERE course_id = :id', ['id' => $course_id]);

      // justification stuff
      $MapKeys = array();
      $MapKeysValues = array();
      $JustificationKeyValues = array();
      $CoPoMappingJustification = DB::select('SELECT mappingJustification FROM justificationcopos WHERE course_id = :id', ['id' => $course_id]);
      $CoPoMappingJustificationId = DB::select('SELECT id FROM justificationcopos WHERE course_id = :id', ['id' => $course_id]);


      if ($CoPoMapping == null) {
        $CoPoMapping = array('');
        return response()->json(['mapping'=>$MapKeyValues, 'justication'=>$MapKeysValues, 'coCount'=>$NoOfCos, 'poCount'=>$NoOfPos]);
      }
      $mapping = $CoPoMapping[0];

      foreach ($mapping as $key => $value) {
        $MapKey =  $value;
      }
      $MapKey = json_decode($MapKey, true);
      for ($i = 0; $i < sizeof($MapKey); $i++) {
        $MapKeyValues[$i] = $MapKey[$i];
      }
      $labelData = "poco";
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

      if (isset($MapKeyValues[$NoOfCos-1])) {
        for ($i=0; $i<$NoOfCos; $i++) {
          // array_push($mappingChart,$labelChart[$i]);
          for ($j=1; $j<=$NoOfPos; $j++) {
            $mappingChart[] = $MapKeyValues[$i][$labelData.$j];
          }
        }
        $requiredPOlabels = array();
        for ($i=0; $i < $NoOfPos; $i++) {
          $requiredPOlabels[] = $labelChart[$i];
        }
        $copoMappingChart = array_chunk($mappingChart, $NoOfPos);

        array_unshift($copoMappingChart, $requiredPOlabels);
        for ($i=0; $i < $NoOfPos; $i++) {
          $pocoMapppingChart[] = array_column($copoMappingChart, $i);
        }
        $pocoMapppingChart = collect($pocoMapppingChart)->filter();


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
              $SlightCorrelation = round(((($correlation[1])/($NoOfCos*$NoOfPos))*100), 2);
            }
            if (array_key_exists(2,$correlation)) {
              $ModerateCorrelation = round(((($correlation[2])/($NoOfCos*$NoOfPos))*100), 2);
            }
            if (array_key_exists(3,$correlation)) {
              $HighCorrelation = round(((($correlation[3])/($NoOfCos*$NoOfPos))*100), 2);
            }
        }


        // getRoute data for justification
        $mappingCoPoJustification = array();
        $mappingCoPoJustification = array_chunk($mappingChart, $NoOfPos);
        $high = array();
        $moderate = array();
        $low = array();
        $row = array();

        $rowsInMapping = $NoOfCos; // Also equals to "sizeof($mappingCoPoJustification)"

        for ($noOfRows=0; $noOfRows < $NoOfCos; $noOfRows++) {
          array_push($high, $row);
          array_push($moderate, $row);
          array_push($low, $row);
        }

        $coLabel = "CO-";
        for ($i = 0, $rowNo = 0;$i < $NoOfCos,$rowNo < $NoOfCos;$i++, $rowNo++) {
          for ($j=0; $j < $NoOfPos; $j++) {
            if (1 == $mappingCoPoJustification[$i][$j]) {
              array_push($low[$rowNo], $j+1);
            }
            elseif (2 == $mappingCoPoJustification[$i][$j]) {
              array_push($moderate[$rowNo], $j+1);
            }
            else {
              array_push($high[$rowNo], $j+1);
            }
          }
        }


        for ($i = 0;$i < $NoOfCos;$i++) {
          array_push($highCorrelation, preg_filter('/^/', 'PO', $high[$i]));
        }
        for ($i = 0;$i < $NoOfCos;$i++) {
          array_push($moderateCorrelation, preg_filter('/^/', 'PO', $moderate[$i]));
        }
        for ($i = 0;$i < $NoOfCos;$i++) {
          array_push($lowCorrelation, preg_filter('/^/', 'PO', $low[$i]));
        }

        for ($i=0; $i < $NoOfCos; $i++) {
          array_unshift($highCorrelation[$i], $coLabel.($i+1));
          array_unshift($moderateCorrelation[$i], $coLabel.($i+1));
          array_unshift($lowCorrelation[$i], $coLabel.($i+1));
        }

        // array string
        for ($i=0; $i < $NoOfCos; $i++) {
          $highCorrelation[$i] = implode(", ",$highCorrelation[$i]);
          $moderateCorrelation[$i] = implode(", ",$moderateCorrelation[$i]);
          $lowCorrelation[$i] = implode(", ",$lowCorrelation[$i]);
        }

        for ($i=0; $i < $NoOfCos; $i++) {
          $highCorrelation[$i][4] = ":";
          $moderateCorrelation[$i][4] = ":";
          $lowCorrelation[$i][4] = ":";
        }

        // removal of unwanted array


        // get route for justificationData

        if ($CoPoMappingJustification == null) {
          $MapKeysValues = array();
        }
        else {
          $mappingJustification = $CoPoMappingJustification[0];

          foreach ($mappingJustification as $key => $value) {
            $MapKeys =  $value;
          }
          $MapKeys = json_decode($MapKeys, true);
          for ($i = 0; $i < sizeof($MapKeys); $i++) {
            $MapKeysValues[$i] = $MapKeys[$i];
          }
        }
      }

      return response()->json(['coCount'=>$NoOfCos, 'poCount'=>$NoOfPos,'mapping'=>$MapKeyValues,'justification'=>$MapKeysValues,
      'SlightCorrelation'=>$SlightCorrelation, 'ModerateCorrelation'=>$ModerateCorrelation, 'HighCorrelation'=>$HighCorrelation,
      'justificationHigh'=>$highCorrelation, 'justificationModern'=>$moderateCorrelation, 'justificationLow'=>$lowCorrelation,
      'justificationId'=>$CoPoMappingJustificationId, 'MappingId' => $CoPoMappingId, 'chart'=>$pocoMapppingChart
    ]);
    }

    public function editPoCoMapping(Request $request, $copoMappingId)
    {
      $status="";
      $message="";
      $request->validate([
          'pocos' => 'required',
      ]);
      try {
          if($poco = Poco::find($copoMappingId))
          {
              $poco->mapping = $request->pocos;
              $poco->save();
          }

          $status = 'success';
          $message = "CO-PO Mapping Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating CO-PO Mapping',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update CO-PO Mapping";

      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function addCoPoMappingJustification(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'course_id' => ['required'],
        'school_id' => ['required'],
        'justification' => ['required'],
      ]);

      try {
        Justificationcopo::create([
              'id' => Str::uuid(),
              'school_id' => $request->school_id,
              'course_id' => $request->course_id,
              'mappingJustification' => json_encode($request->justification),
              'saved_by' => auth()->user()->id,
        ]);
        $course = course_code::find($request->course_id);
          if ($course->trackingNo == 10) {
            $course->trackingNo = 11;
            $course->save();
          }
        $status = 'success';
        $message = "CO-PO MappingJustification Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding CO-PO MappingJustification',$e->getMessage());
        $status = 'error';
        $message = "Unable to add CO-PO Justification";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function editCoPoMappingJustification(Request $request, $copoMappingJustificationId)
    {
      $status="";
      $message="";
      $request->validate([
          'justification' => 'required',
      ]);
      try {
          if($copo = Justificationcopo::find($copoMappingJustificationId))
          {
              $copo->mappingJustification = $request->justification;
              $copo->save();
          }

          $status = 'success';
          $message = "CO-PO Mapping Justification Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating Justification CO-PO Mapping',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update Justification CO-PO Mapping";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function addPeoCoMapping(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'school_id' => ['required'],
        'course_id' => ['required'],
        'copeos' => ['required']
      ]);
      try {
          Copeo::create([
                'id' => Str::uuid(),
                'school_id' => $request->school_id,
                'course_id' => $request->course_id,
                'mapping' => json_encode($request->copeos),
                'saved_by' => auth()->user()->id,
          ]);
        $status = 'success';
        $message = "CO-PEO Mapping Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding CO-PEO Mapping',$e->getMessage());
        $status = 'error';
        $message = "Unable to Add CO-PEO";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function getPeoCoMapping(Request $request, $course_id, $school_id)
    {
        $peocoMapppingChart = array();
        $MapKey = array();
        $MapKeyValues = array();
        $NoOfCos = Co::where('course_id', $course_id)->count();
        $NoOfPeos = Peo::where('school_id', $school_id)->count();
        $CoPeoMapping = DB::select('SELECT mapping FROM copeos WHERE course_id = :id', ['id' => $course_id]);
        $CoPeoMappingId = DB::select('SELECT id FROM copeos WHERE course_id = :id', ['id' => $course_id]);

        // justification stuff
        $MapKeys = array();
        $MapKeysValues = array();
        $JustificationKeyValues = array();
        $CoPeoMappingJustification = DB::select('SELECT mappingJustification FROM justificationcopeos WHERE course_id = :id', ['id' => $course_id]);
        $CoPeoMappingJustificationId = DB::select('SELECT id FROM justificationcopeos WHERE course_id = :id', ['id' => $course_id]);


        if ($CoPeoMapping == null) {
          $CoPeoMapping = array('');
          return response()->json(['mapping'=>$MapKeyValues, 'justication'=>$MapKeysValues, 'coCount'=>$NoOfCos, 'peoCount'=>$NoOfPeos]);
        }
        $mapping = $CoPeoMapping[0];

        foreach ($mapping as $key => $value) {
          $MapKey =  $value;
        }
        $MapKey = json_decode($MapKey, true);
        for ($i = 0; $i < sizeof($MapKey); $i++) {
          $MapKeyValues[$i] = $MapKey[$i];
        }
        $labelData = "copeo";
        $labelChart = array("PEO1", "PEO2", "PEO3", "PEO4", "PEO5", "PEO6", "PEO7", "PEO8", "PEO9", "PEO10", "PEO11", "PEO12", "PEO13", "PEO14", "PEO15");

        $mappingChart = array();

        // correlation
        $highCorrelation = array();
        $moderateCorrelation = array();
        $lowCorrelation = array();

        $SlightCorrelation = 0;
        $ModerateCorrelation = 0;
        $HighCorrelation = 0;

        if (isset($MapKeyValues[$NoOfCos-1])) {
          for ($i=0; $i<$NoOfCos; $i++) {
            // array_push($mappingChart,$labelChart[$i]);
            for ($j=1; $j<=$NoOfPeos; $j++) {
              $mappingChart[] = $MapKeyValues[$i][$labelData.$j];
            }
          }
          $requiredPEOlabels = array();
          for ($i=0; $i < $NoOfPeos; $i++) {
            $requiredPEOlabels[] = $labelChart[$i];
          }
          $copeoMappingChart = array_chunk($mappingChart, $NoOfPeos);

          array_unshift($copeoMappingChart, $requiredPEOlabels);
          for ($i=0; $i < $NoOfPeos; $i++) {
            $peocoMapppingChart[] = array_column($copeoMappingChart, $i);
          }
          $peocoMapppingChart = collect($peocoMapppingChart)->filter();


          $checkEmptyPeo = 0;
          for ($i=0; $i < sizeof($mappingChart); $i++) {
              if (null == $mappingChart[$i]) {
                $checkEmptyPeo = 1;
              }
          }

          if ($checkEmptyPeo == 0) {
            // Counting Correlation
            $correlation = array_count_values($mappingChart);

              if (array_key_exists(1,$correlation)) {
                $SlightCorrelation = round(((($correlation[1])/($NoOfCos*$NoOfPeos))*100), 2);
              }
              if (array_key_exists(2,$correlation)) {
                $ModerateCorrelation = round(((($correlation[2])/($NoOfCos*$NoOfPeos))*100), 2);
              }
              if (array_key_exists(3,$correlation)) {
                $HighCorrelation = round(((($correlation[3])/($NoOfCos*$NoOfPeos))*100), 2);
              }
          }


          // getRoute data for justification
          $mappingCoPeoJustification = array();
          $mappingCoPeoJustification = array_chunk($mappingChart, $NoOfPeos);
          $high = array();
          $moderate = array();
          $low = array();
          $row = array();

          $rowsInMapping = $NoOfCos; // Also equals to "sizeof($mappingCoPeoJustification)"

          for ($noOfRows=0; $noOfRows < $NoOfCos; $noOfRows++) {
            array_push($high, $row);
            array_push($moderate, $row);
            array_push($low, $row);
          }

          $coLabel = "CO-";
          for ($i = 0, $rowNo = 0;$i < $NoOfCos,$rowNo < $NoOfCos;$i++, $rowNo++) {
            for ($j=0; $j < $NoOfPeos; $j++) {
              if (1 == $mappingCoPeoJustification[$i][$j]) {
                array_push($low[$rowNo], $j+1);
              }
              elseif (2 == $mappingCoPeoJustification[$i][$j]) {
                array_push($moderate[$rowNo], $j+1);
              }
              else {
                array_push($high[$rowNo], $j+1);
              }
            }
          }


          for ($i = 0;$i < $NoOfCos;$i++) {
            array_push($highCorrelation, preg_filter('/^/', 'PEO', $high[$i]));
          }
          for ($i = 0;$i < $NoOfCos;$i++) {
            array_push($moderateCorrelation, preg_filter('/^/', 'PEO', $moderate[$i]));
          }
          for ($i = 0;$i < $NoOfCos;$i++) {
            array_push($lowCorrelation, preg_filter('/^/', 'PEO', $low[$i]));
          }

          for ($i=0; $i < $NoOfCos; $i++) {
            array_unshift($highCorrelation[$i], $coLabel.($i+1));
            array_unshift($moderateCorrelation[$i], $coLabel.($i+1));
            array_unshift($lowCorrelation[$i], $coLabel.($i+1));
          }

          // array string
          for ($i=0; $i < $NoOfCos; $i++) {
            $highCorrelation[$i] = implode(", ",$highCorrelation[$i]);

            $moderateCorrelation[$i] = implode(", ",$moderateCorrelation[$i]);

            $lowCorrelation[$i] = implode(", ",$lowCorrelation[$i]);
          }

          for ($i=0; $i < $NoOfCos; $i++) {
            $highCorrelation[$i][4] = ":";
            $moderateCorrelation[$i][4] = ":";
            $lowCorrelation[$i][4] = ":";
          }


          // get route for justificationData

          if ($CoPeoMappingJustification == null) {
            $MapKeysValues = array();
          }
          else {
            $mappingJustification = $CoPeoMappingJustification[0];

            foreach ($mappingJustification as $key => $value) {
              $MapKeys =  $value;
            }
            $MapKeys = json_decode($MapKeys, true);
            for ($i = 0; $i < sizeof($MapKeys); $i++) {
              $MapKeysValues[$i] = $MapKeys[$i];
            }
          }
        }

        return response()->json(['coCount'=>$NoOfCos, 'peoCount'=>$NoOfPeos,'mapping'=>$MapKeyValues,'justification'=>$MapKeysValues,
        'SlightCorrelation'=>$SlightCorrelation, 'ModerateCorrelation'=>$ModerateCorrelation, 'HighCorrelation'=>$HighCorrelation,
        'justificationHigh'=>$highCorrelation, 'justificationModern'=>$moderateCorrelation, 'justificationLow'=>$lowCorrelation,
        'justificationId'=>$CoPeoMappingJustificationId, 'MappingId' => $CoPeoMappingId, 'chart'=>$peocoMapppingChart
        ]);
    }

    public function editPeoCoMapping(Request $request, $copeoMappingId)
    {
      $status="";
      $message="";
      $request->validate([
          'copeos' => 'required',
      ]);
      try {
          if($copeo = copeo::find($copeoMappingId))
          {
              $copeo->mapping = $request->copeos;
              $copeo->save();
          }

          $status = 'success';
          $message = "CO-PEO Mapping Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating CO-PEO Mapping',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update CO-PEO Mapping";

      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function addCoPeoMappingJustification(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'course_id' => ['required'],
        'school_id' => ['required'],
        'justification' => ['required'],
      ]);

      try {
        Justificationcopeo::create([
              'id' => Str::uuid(),
              'school_id' => $request->school_id,
              'course_id' => $request->course_id,
              'mappingJustification' => json_encode($request->justification),
              'saved_by' => auth()->user()->id,
        ]);
        $course = course_code::find($request->course_id);
          if ($course->trackingNo == 11) {
            $course->trackingNo = 12;
            $course->save();
          }
        $status = 'success';
        $message = "CO-PEO MappingJustification Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding CO-PEO MappingJustification',$e->getMessage());
        $status = 'error';
        $message = "Unable to add CO-PEO Justification";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function editCoPeoMappingJustification(Request $request, $copeoMappingJustificationId)
    {
      $status="";
      $message="";
      $request->validate([
          'justification' => 'required',
      ]);
      try {
          if($copeo = Justificationcopeo::find($copeoMappingJustificationId))
          {
              $copeo->mappingJustification = $request->justification;
              $copeo->save();
          }

          $status = 'success';
          $message = "CO-PEO Mapping Justification Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating Justification CO-PEO Mapping',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update Justification CO-PEO Mapping";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

}
