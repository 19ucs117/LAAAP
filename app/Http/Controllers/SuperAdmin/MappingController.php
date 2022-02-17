<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;


use App\Models\School;
use App\Models\Peo;
use App\Models\Po;
use App\Models\Peopo;
use App\Models\Missionvisionpo;
use App\Models\missionvisionpeo;
use App\Models\justificationpeopo;
use App\Models\Copeo;
use App\Models\Peopso;
use App\Models\Psopo;
use App\Models\Poco;


class MappingController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(['auth', 'superadmin']);
    }

    public function addPeo(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'school_id' => 'required',
        'peos' => 'required'
      ]);
      try {
        $labelNumber = 0;
        if ($isAnItem = Peo::where('school_id', $request->school_id)->count()) {
          $labelNumber = $isAnItem;
        }
        $peoLabel = 'PEO - ';
        for ($i = 0; $i < sizeof($request->peos); $i++) {
          $labelNumber = $labelNumber + 1;
          Peo::create([
            'id' => Str::uuid(),
            'school_id' => $request->school_id,
            'labelNo' => $peoLabel . ($labelNumber),
            'description' => $request->peos[$i]['peo'],
          ]);
        }
        $status = 'success';
        $message = "PEO Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding PEO',$e->getMessage());
        $status = 'error';
        $message = "Unable to Add PEO";
      }

      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function editPeo(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
          'school_id' => 'required',
          'peos' => 'required',
      ]);
      try {
          for($i = 0; $i < sizeof($request->peos);$i++){
            if($peo = Peo::find($request->peos[$i]['id']))
            {
                $peo->description = $request->peos[$i]['peo'];
                $peo->save();
            }
          }
          $status = 'success';
          $message = "PEO Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating PEO',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update PEO";

      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function deletePeo(Request $request, $peoId, $labelNo)
    {
      $status="";
      $message="";
      $MapKeyValues = array();

      try {

          if ($peo = Peo::find($peoId)) {

            $PeoPoMapping = DB::select('SELECT mapping FROM peopos WHERE school_id = :id', ['id' => $peo->school_id]);
            $PeoPsoMapping = DB::select('SELECT mapping FROM peopsos WHERE school_id = :id', ['id' => $peo->school_id]);
            $PeoCoMapping = DB::select('SELECT mapping FROM copeos WHERE school_id = :id', ['id' => $peo->school_id]);

            if ($PeoPoMapping != null) {
              // deleting peo[$labelNo] from peopomapping
              $peopoMappingId = DB::select('SELECT id FROM peopos WHERE school_id = :id', ['id' => $peo->school_id]);
              $MapKey = array();
              $mapping = $PeoPoMapping[0];
              foreach ($mapping as $key => $value) {
                $MapKey =  $value;
              }
              $MapKey = json_decode($MapKey, true);
              for ($i = 0; $i < sizeof($MapKey); $i++) {
                $MapKeyValues[$i] = $MapKey[$i];
              }
              unset($MapKeyValues[$labelNo]);
              if($peopo = Peopo::find($peopoMappingId[0]->id))
              {
                  $peopo->mapping = array_values($MapKeyValues);
                  $peopo->save();
              }
          }

          if ($PeoPsoMapping != null) {
            $peopsoMappingId = DB::select('SELECT id FROM peopsos WHERE school_id = :id', ['id' => $peo->school_id]);
            $peoCount = Peo::where('school_id', $peo->school_id)->count();
            $peopsoMapKey = array();
            for ($PeoPsocounter=0; $PeoPsocounter < sizeof($peopsoMappingId); $PeoPsocounter++) {
              $PeoPsomapping = $PeoPsoMapping[$PeoPsocounter];
              foreach ($PeoPsomapping as $key => $value) {
                $peopsoMapKey =  $value;
              }
              $peopsoMapKey = json_decode($peopsoMapKey, true);
              for ($i = 0; $i < sizeof($peopsoMapKey); $i++) {
                $peopsoMapKeyValues[$i] = $peopsoMapKey[$i];
              }
              unset($peopsoMapKeyValues[$labelNo]);
              if($peopso = Peopso::find($peopsoMappingId[$PeoPsocounter]->id))
              {
                  $peopso->mapping = array_values($peopsoMapKeyValues);
                  $peopso->save();
              }
            }
          }
          $labelNo = $labelNo + 1;
          if ($PeoCoMapping != null) {
            // deleting peo[$labelNo] from peocomapping
              // $labelNo = $labelNo + 1;
              $peocoMappingId = DB::select('SELECT id FROM copeos WHERE school_id = :id', ['id' => $peo->school_id]);

              $peocoMapKey = array();

              for ($PeoCoCounter=0; $PeoCoCounter < sizeof($peocoMappingId); $PeoCoCounter++) {
                $PeoComapping = $PeoCoMapping[$PeoCoCounter];
                foreach ($PeoComapping as $key => $value) {
                  $peocoMapKey =  $value;
                }
                $peocoMapKey = json_decode($peocoMapKey, true);
                for ($i = 0; $i < sizeof($peocoMapKey); $i++) {
                  $peocoMapKeyValues[$i] = $peocoMapKey[$i];
                }
                for ($i=0; $i < sizeof($peocoMapKey); $i++) {
                  $peocoMapKeyValues[$i]['copeo'.$labelNo] = $peocoMapKeyValues[$i]['copeo'.($labelNo+1)];
                  if ($peocoMapKeyValues[$i]['copeo'.($labelNo+1)] != null) {
                    for ($j=$labelNo+1; $j < $peoCount; $j++) {
                      if ($peocoMapKeyValues[$i]['copeo'.($j)] != null) {
                        $peocoMapKeyValues[$i]['copeo'.($j)] = $peocoMapKeyValues[$i]['copeo'.($j+1)];
                      }
                      else {
                        $peocoMapKeyValues[$i+1]['copeo'.($labelNo+1)] = null;
                      }
                    }
                  }
                  $peocoMapKeyValues[$i]['copeo'.($peoCount)] = null;
                }
                if($peoco = Copeo::find($peocoMappingId[$PeoCoCounter]->id))
                {
                    $peoco->mapping = $peocoMapKeyValues;
                    $peoco->save();
                }
              }
          }

          // delete PEO description
            $peo->delete();

        }

          $status = 'success';
          $message = "PEO Deleted Successfully";
      } catch (Exception $e) {
          Log::warning('Error Deleting PEO',$e->getMessage());
          $status = 'error';
          $message = "Unable to Delete PEO";
      }

      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function addPo(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
        'school_id' => 'required',
        'pos' => 'required',
      ]);
      try {
          $labelNumber = 0;
          if ($isAnItem = Po::where('school_id', $request->school_id)->count()) {
            $labelNumber = $isAnItem;
          }
          $poLabel = 'PO - ';
          for($i = 0; $i < sizeof($request->pos);$i++){
            $labelNumber = $labelNumber+1;
            Po::create([
                'id' => Str::uuid(),
                'school_id' => $request->school_id,
                'labelNo' => $poLabel.($labelNumber),
                'description' => $request->pos[$i]['po'],
            ]);
          }
          $status = 'success';
          $message = "PO Added Successfully";
      } catch (Exception $e) {
          Log::warning('Error Adding PO',$e->getMessage());
          $status = 'error';
          $message = "Unable to Add PO";

      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function editPo(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
          'school_id' => 'required',
          'pos' => 'required',
      ]);
      try {
          for($i = 0; $i < sizeof($request->pos);$i++){
            if($po = Po::find($request->pos[$i]['id']))
            {
                $po->description = $request->pos[$i]['po'];
                $po->save();
            }
          }
          $status = 'success';
          $message = "PO Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating PO',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update PO";

      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function deletePo(Request $request, $poId, $labelNo)
    {
      $status="";
      $message="";
      $MapKeyValues = array();
      $required_peopoLabel = array();
      try {
          if ($po = Po::find($poId)) {
            $PeoPoMapping = DB::select('SELECT mapping FROM peopos WHERE school_id = :id', ['id' => $po->school_id]);
            $PoPsoMapping = DB::select('SELECT mapping FROM psopos WHERE school_id = :id', ['id' => $po->school_id]);
            $PoCoMapping = DB::select('SELECT mapping FROM pocos WHERE school_id = :id', ['id' => $po->school_id]);
            $poCount = Po::where('school_id', $po->school_id)->count();
            if ($PeoPoMapping != null) {
              $peopoMappingId = DB::select('SELECT id FROM peopos WHERE school_id = :id', ['id' => $po->school_id]);
              $MapKey = array();
              $mapping = $PeoPoMapping[0];
              $mappingUpdate = array();
              foreach ($mapping as $key => $value) {
                $MapKey =  $value;
              }
              $MapKey = json_decode($MapKey, true);
              for ($i = 0; $i < sizeof($MapKey); $i++) {
                $MapKeyValues[$i] = $MapKey[$i];
              }

              for ($i=0; $i<sizeof($MapKey);) {
                for($j=$labelNo;$j<=$poCount;) {
                  $MapKeyValues[$i]['peopo'.($j)] = $MapKeyValues[$i]['peopo'.($j+1)];
                  $j++;
                }
                $i++;
              }
              if($peopo = Peopo::find($peopoMappingId[0]->id))
              {
                  $peopo->mapping = $MapKeyValues;
                  $peopo->save();
              }
            }

            if ($PoCoMapping != null) {
              $pocoMappingId = DB::select('SELECT id FROM pocos WHERE school_id = :id', ['id' => $po->school_id]);
              $PoCoMapKey = array();
              for ($PoCoCounter=0; $PoCoCounter < sizeof($pocoMappingId); $PoCoCounter++) {
                $PoComapping = $PoCoMapping[$PoCoCounter];
                foreach ($PoComapping as $key => $value) {
                  $PoCoMapKey =  $value;
                }
                $PoCoMapKey = json_decode($PoCoMapKey, true);
                for ($i = 0; $i < sizeof($PoCoMapKey); $i++) {
                  $PoCoMapKeyValues[$i] = $PoCoMapKey[$i];
                }
                for ($i=0; $i<sizeof($PoCoMapKey);) {
                  for($j=$labelNo;$j<=$poCount;) {
                    $PoCoMapKeyValues[$i]['poco'.($j)] = $PoCoMapKeyValues[$i]['poco'.($j+1)];
                    $j++;
                  }
                  $i++;
                }
                if($poco = Poco::find($pocoMappingId[$PoCoCounter]->id))
                {
                    $poco->mapping = $PoCoMapKeyValues;
                    $poco->save();
                }
              }
            }

            $labelNo = $labelNo - 1;
            if ($PoPsoMapping != null) {
              $popsoMappingId = DB::select('SELECT id FROM psopos WHERE school_id = :id', ['id' => $po->school_id]);
              $popsoMapKey = array();
              for ($PoPsoCounter=0; $PoPsoCounter < sizeof($popsoMappingId); $PoPsoCounter++) {
                $PoPsomapping = $PoPsoMapping[$PoPsoCounter];
                foreach ($PoPsomapping as $key => $value) {
                  $popsoMapKey =  $value;
                }
                $popsoMapKey = json_decode($popsoMapKey, true);
                for ($i = 0; $i < sizeof($popsoMapKey); $i++) {
                  $popsoMapKeyValues[$i] = $popsoMapKey[$i];
                }
                unset($popsoMapKeyValues[$labelNo]);
                if($popso = Psopo::find($popsoMappingId[$PoPsoCounter]->id))
                {
                    $popso->mapping = array_values($popsoMapKeyValues);
                    $popso->save();
                }
              }
            }

            $po->delete();
          }

          $status = 'success';
          $message = "PO Deleted Successfully";
      } catch (Exception $e) {
          Log::warning('Error Deleting PO',$e->getMessage());
          $status = 'error';
          $message = "Unable to Delete PO";
      }

      return response()->json(['message'=>$message, 'status'=>$status]);
    }

    public function addVisionAndMissionPeoMapping(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'school_id' => ['required'],
        'missionvisionpeos' => ['required'],
      ]);

      try {
        missionvisionpeo::create([
              'id' => Str::uuid(),
              'school_id' => $request->school_id,
              'mapping' => json_encode($request->missionvisionpeos),
              'saved_by' => auth()->user()->id,
        ]);
        $status = 'success';
        $message = "VisionAndMission-PEO Mapping Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding VisionAndMission-PEO Mapping',$e->getMessage());
        $status = 'error';
        $message = "Unable to Add VisionAndMission-PEO";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function getVisionAndMissionPeoMapping(Request $request, $school_id)
    {
      $visionAndmissionpeoMapppingChart = array();
      $MapKey = array();
      $MapKeyValues = array();
      $NoVisionMission = 5;
      $NoOfPeos = Peo::where('school_id', $school_id)->count();
      $VisionAndMissionPeoMapping = DB::select('SELECT mapping FROM missionvisionpeos WHERE school_id = :id', ['id' => $school_id]);
      $VisionAndMissionPeoMappingId = DB::select('SELECT id FROM missionvisionpeos WHERE school_id = :id', ['id' => $school_id]);
      if ($VisionAndMissionPeoMapping == null) {
        $VisionAndMissionPeoMapping = array('');
        return response()->json(['mapping'=>$MapKeyValues, 'visionMissionCount'=>$NoVisionMission, 'peoCount'=>$NoOfPeos]);
      }
      $mapping = $VisionAndMissionPeoMapping[0];

      foreach ($mapping as $key => $value) {
        $MapKey =  $value;
      }
      $MapKey = json_decode($MapKey, true);
      for ($i = 0; $i < sizeof($MapKey); $i++) {
        $MapKeyValues[$i] = $MapKey[$i];
      }
      $labelData = "missionvisionpeo";
      $labelChart = array("PEO1", "PEO2", "PEO3", "PEO4", "PEO5", "PEO6", "PEO7", "PEO8", "PEO9", "PEO10", "PEO11", "PEO12", "PEO13", "PEO14", "PEO15");
      // $labelChart = array("PEO1", "PEO2", "PEO3", "PEO4", "PEO5", "PEO6", "PEO7", "PEO8", "PEO9", "PEO10", "PEO11", "PEO12", "PEO13", "PEO14", "PEO15");

      $mappingChart = array();

      for ($i=0; $i<$NoVisionMission; $i++) {
        // array_push($mappingChart,$labelChart[$i]);
        for ($j=1; $j<=$NoOfPeos; $j++) {
          $mappingChart[] = $MapKeyValues[$i][$labelData.$j];
        }
      }
      $requiredPEOlabels = array();
      for ($i=0; $i < $NoOfPeos; $i++) {
        $requiredPEOlabels[] = $labelChart[$i];
      }
      $peopoMappingChart = array_chunk($mappingChart, $NoOfPeos);

      array_unshift($peopoMappingChart, $requiredPEOlabels);
      for ($i=0; $i < $NoOfPeos; $i++) {
        $visionAndmissionpeoMapppingChart[] = array_column($peopoMappingChart, $i);
      }
      $visionAndmissionpeoMapppingChart = collect($visionAndmissionpeoMapppingChart)->filter();

      // Counting Correlation
      $SlightCorrelation = 0;
      $ModerateCorrelation = 0;
      $HighCorrelation = 0;

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
            $SlightCorrelation = round(((($correlation[1])/($NoVisionMission*$NoOfPeos))*100), 2);
          }
          if (array_key_exists(2,$correlation)) {
            $ModerateCorrelation = round(((($correlation[2])/($NoVisionMission*$NoOfPeos))*100), 2);
          }
          if (array_key_exists(3,$correlation)) {
            $HighCorrelation = round(((($correlation[3])/($NoVisionMission*$NoOfPeos))*100), 2);
          }
      }
      // return response()->json(['correlation'=>$correlation]);
      return response()->json(['mapping'=>$MapKeyValues,'SlightCorrelation'=>$SlightCorrelation,
      'ModerateCorrelation'=>$ModerateCorrelation, 'HighCorrelation'=>$HighCorrelation,
      'visionMissionCount'=>$NoVisionMission, 'peoCount'=>$NoOfPeos,
      'id' => $VisionAndMissionPeoMappingId, 'chart'=>$visionAndmissionpeoMapppingChart]);
    }

    public function editVisionAndMissionPeoMapping(Request $request, $VisionAndMissionPeoMappingId)
    {
      $status="";
      $message="";
      $request->validate([
          'missionvisionpeos' => 'required',
      ]);
      try {
          if($VisionAndMissionPeoMapping = missionvisionpeo::find($VisionAndMissionPeoMappingId))
          {
              $VisionAndMissionPeoMapping->mapping = $request->missionvisionpeos;
              $VisionAndMissionPeoMapping->save();
          }

          $status = 'success';
          $message = "VisionAndMission-PEO Mapping Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating VisionAndMission-PEO Mapping',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update VisionAndMission-PEO Mapping";

      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function addVisionMissionPeoMappingJustification(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'school_id' => ['required'],
        'justification' => ['required'],
      ]);

      try {
        Justificationvisionmissionpeo::create([
              'id' => Str::uuid(),
              'school_id' => $request->school_id,
              'mappingJustification' => json_encode($request->justification),
              'saved_by' => auth()->user()->id,
        ]);
        $status = 'success';
        $message = "VISIONMISSION-PEO MappingJustification Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding VISIONMISSION-PEO MappingJustification',$e->getMessage());
        $status = 'error';
        $message = "Unable to add VISIONMISSION-PEO Justification";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function editVisionMissionPeoMappingJustification(Request $request, $visionmissionpeoMappingJustificationId)
    {
      $status="";
      $message="";
      $request->validate([
          'justification' => 'required',
      ]);
      try {
          if($visionmissionpeo = Justificationvisionmissionpeo::find($visionmissionpeoMappingJustificationId))
          {
              $visionmissionpeo->mappingJustification = $request->justification;
              $visionmissionpeo->save();
          }

          $status = 'success';
          $message = "VISIONMISSION-PEO Mapping Justification Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating Justification VISIONMISSION-PEO Mapping',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update Justification VISIONMISSION-PEO Mapping";
      }

      return response()->json(['status' => $status,'message'=>$message]);
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
      $NoOfPeos = Peo::where('school_id', $school_id)->count();
      $NoOfPos = Po::where('school_id', $school_id)->count();
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


    public function addPeoPoMappingJustification(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'school_id' => ['required'],
        'justification' => ['required'],
      ]);

      try {
        justificationpeopo::create([
              'id' => Str::uuid(),
              'school_id' => $request->school_id,
              'mappingJustification' => json_encode($request->justification),
              'saved_by' => auth()->user()->id,
        ]);
        $status = 'success';
        $message = "PEO-PO MappingJustification Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding PEO-PO MappingJustification',$e->getMessage());
        $status = 'error';
        $message = "Unable to add PEO-PO Justification";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function editPeoPoMappingJustification(Request $request, $peopoMappingJustificationId)
    {
      $status="";
      $message="";
      $request->validate([
          'justification' => 'required',
      ]);
      try {
          if($peopo = justificationpeopo::find($peopoMappingJustificationId))
          {
              $peopo->mappingJustification = $request->justification;
              $peopo->save();
          }

          $status = 'success';
          $message = "PEO-PO Mapping Justification Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating Justification PEO-PO Mapping',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update Justification PEO-PO Mapping";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

}
