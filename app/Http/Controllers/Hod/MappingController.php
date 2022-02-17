<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

use App\Models\course_code;
use App\Models\school;
use App\Models\Department;
use App\Models\program;
use App\Models\User;
use App\Models\Peo;
use App\Models\Po;
use App\Models\Pso;
use App\Models\Missionvisionpso;
use App\Models\Psopo;
use App\Models\Peopso;
use App\Models\copso;



class MappingController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(['auth', 'hod']);
    }

    public function addPso(Request $request)
    {
      $status="";
      $message="";
      $request->validate([
          'department_id' => ['required'],
          'program_id' => ['required'],
          'psos' => ['required']
      ]);
      try {
          $labelNumber = 0;
          if ($isAnItem = Pso::where('program_id', $request->program_id)->count()) {
            $labelNumber = $isAnItem;
          }
          $psoLabel = 'PSO - ';
          for($i = 0; $i < sizeof($request->psos);$i++){
            $labelNumber = $labelNumber + 1;
            Pso::create([
                'id' => Str::uuid(),
                'department_id' => $request->department_id,
                'program_id' => $request->program_id,
                'labelNo' => $psoLabel.($labelNumber),
                'description' => $request->psos[$i]['pso'],
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]);
          }
          $status = 'success';
          $message = "PSO Added Successfully";
      } catch (Exception $e) {
          Log::warning('Error Adding PSO',$e->getMessage());
          $status = 'error';
          $message = "Unable to Add PSO";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function editPso(Request $request)
    {
      $status="";
      $message="";
      try {
          for($i = 0; $i < sizeof($request->psos);$i++){
            if($data = Pso::find($request->psos[$i]['id']))
            {
                $data->description = $request->psos[$i]['pso'];
                $data->save();
            }
          }
          $status = 'success';
          $message = "PSO Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating PSO',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update PSO";

      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function deletePso(Request $request, $psoId, $labelNo)
    {
      $status="";
      $message="";
      try {
          if ($pso = Pso::find($psoId)) {
            $PeoPsoMapping = DB::select('SELECT mapping FROM peopsos WHERE program_id = :id', ['id' => $pso->program_id]);
            $PoPsoMapping = DB::select('SELECT mapping FROM psopos WHERE program_id = :id', ['id' => $pso->program_id]);
            $CoPsoMapping = DB::select('SELECT mapping FROM copsos WHERE program_id = :id', ['id' => $pso->program_id]);
            $psoCount = Pso::where('program_id', $pso->program_id)->count();
            if ($PoPsoMapping != null) {
              $popsoMappingId = DB::select('SELECT id FROM psopos WHERE program_id = :id', ['id' => $pso->program_id]);
              $psopoMapKey = array();
              $PsoPomapping = $PoPsoMapping[0];
              $PsoPomappingUpdate = array();
              foreach ($PsoPomapping as $key => $value) {
                $psopoMapKey =  $value;
              }
              $psopoMapKey = json_decode($psopoMapKey, true);
              for ($i = 0; $i < sizeof($psopoMapKey); $i++) {
                $psopoMapKeyValues[$i] = $psopoMapKey[$i];
              }

              for ($i=0; $i<sizeof($psopoMapKey);) {
                for($j=$labelNo;$j<=$psoCount;) {
                  $psopoMapKeyValues[$i]['psopo'.($j)] = $psopoMapKeyValues[$i]['psopo'.($j+1)];
                  $j++;
                }
                $i++;
              }
              if($psopo = Psopo::find($popsoMappingId[0]->id))
              {
                  $psopo->mapping = $psopoMapKeyValues;
                  $psopo->save();
              }
            }

            if ($PeoPsoMapping != null) {
              $peopsoMappingId = DB::select('SELECT id FROM peopsos WHERE program_id = :id', ['id' => $pso->program_id]);
              $psopeoMapKey = array();
              $PsoPeomapping = $PeoPsoMapping[0];
              $PsoPeomappingUpdate = array();
              foreach ($PsoPeomapping as $key => $value) {
                $psopeoMapKey =  $value;
              }
              $psopeoMapKey = json_decode($psopeoMapKey, true);
              for ($i = 0; $i < sizeof($psopeoMapKey); $i++) {
                $psopeoMapKeyValues[$i] = $psopeoMapKey[$i];
              }

              for ($i=0; $i<sizeof($psopeoMapKey);) {
                for($j=$labelNo;$j<=$psoCount;) {
                  $psopeoMapKeyValues[$i]['peopso'.($j)] = $psopeoMapKeyValues[$i]['peopso'.($j+1)];
                  $j++;
                }
                $i++;
              }
              if($peopso = Peopso::find($peopsoMappingId[0]->id))
              {
                  $peopso->mapping = $psopeoMapKeyValues;
                  $peopso->save();
              }
            }

            if ($CoPsoMapping != null) {
              $copsoMappingId = DB::select('SELECT id FROM copsos WHERE program_id = :id', ['id' => $pso->program_id]);
              $psocoMapKey = array();
              for ($CoPsoCounter=0; $CoPsoCounter < sizeof($copsoMappingId); $CoPsoCounter++) {
                $PsoComapping = $CoPsoMapping[$CoPsoCounter];
                $PsoComappingUpdate = array();
                foreach ($PsoComapping as $key => $value) {
                  $psocoMapKey =  $value;
                }
                $psocoMapKey = json_decode($psocoMapKey, true);
                for ($i = 0; $i < sizeof($psocoMapKey); $i++) {
                  $psocoMapKeyValues[$i] = $psocoMapKey[$i];
                }

                for ($i=0; $i<sizeof($psocoMapKey);) {
                  for($j=$labelNo;$j<=$psoCount;) {
                    $psocoMapKeyValues[$i]['copso'.($j)] = $psocoMapKeyValues[$i]['copso'.($j+1)];
                    $j++;
                  }
                  $i++;
                }
                if($copso = copso::find($copsoMappingId[$CoPsoCounter]->id))
                {
                    $copso->mapping = $psocoMapKeyValues;
                    $copso->save();
                }
              }
            }

            $pso->delete();
          }
          $status = 'success';
          $message = "PSO Deleted Successfully";
      } catch (Exception $e) {
          Log::warning('Error Deleting PSO',$e->getMessage());
          $status = 'error';
          $message = "Unable to Delete PSO";

      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function addPoPsoMapping(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'program_id' => ['required'],
        'school_id' => ['required'],
        'psopos' => ['required'],
      ]);

      try {
        Psopo::create([
              'id' => Str::uuid(),
              'school_id' => $request->school_id,
              'program_id' => $request->program_id,
              'mapping' => json_encode($request->psopos),
              'saved_by' => auth()->user()->id,
        ]);
        $status = 'success';
        $message = "PO-PSO Mapping Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding PO-PSO Mapping',$e->getMessage());
        $status = 'error';
        $message = "Unable to PO-PSO";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function getPoPsoMapping(Request $request, $program_id, $school_id)
    {
      $popsoMapppingChart = array();
      $MapKey = array();
      $MapKeyValues = array();
      $NoOfPos = Po::where('school_id', $school_id)->count();
      $NoOfPsos = Pso::where('program_id', $program_id)->count();
      $PoPsoMapping = DB::select('SELECT mapping FROM psopos WHERE program_id = :id', ['id' => $program_id]);
      $PoPsoMappingId = DB::select('SELECT id FROM psopos WHERE program_id = :id', ['id' => $program_id]);
      if ($PoPsoMapping == null) {
        $PoPsoMapping = array('');
        return response()->json(['mapping'=>$MapKeyValues, 'poCount'=>$NoOfPos, 'psoCount'=>$NoOfPsos]);
      }
      $mapping = $PoPsoMapping[0];

      foreach ($mapping as $key => $value) {
        $MapKey =  $value;
      }
      $MapKey = json_decode($MapKey, true);
      for ($i = 0; $i < sizeof($MapKey); $i++) {
        $MapKeyValues[$i] = $MapKey[$i];
      }
      $labelData = "psopo";
      $labelChart = array("PSO1", "PSO2", "PSO3", "PSO4", "PSO5", "PSO6", "PSO7", "PSO8", "PSO9", "PSO10", "PSO11", "PSO12", "PSO13", "PSO14", "PSO15");
      // $labelChart = array("PSO1", "PSO2", "PSO3", "PSO4", "PSO5", "PSO6", "PSO7", "PSO8", "PSO9", "PSO10", "PSO11", "PSO12", "PSO13", "PSO14", "PEO15");

      $mappingChart = array();

      $SlightCorrelation = 0;
      $ModerateCorrelation = 0;
      $HighCorrelation = 0;

      if (isset($MapKeyValues[$NoOfPos-1])) {
        for ($i=0; $i<$NoOfPos; $i++) {
          // array_push($mappingChart,$labelChart[$i]);
          for ($j=1; $j<=$NoOfPsos; $j++) {
            $mappingChart[] = $MapKeyValues[$i][$labelData.$j];
          }
        }
        $requiredPSOlabels = array();
        for ($i=0; $i < $NoOfPsos; $i++) {
          $requiredPSOlabels[] = $labelChart[$i];
        }
        $psopoMappingChart = array_chunk($mappingChart, $NoOfPsos);

        array_unshift($psopoMappingChart, $requiredPSOlabels);
        for ($i=0; $i < $NoOfPsos; $i++) {
          $popsoMapppingChart[] = array_column($psopoMappingChart, $i);
        }
        $popsoMapppingChart = collect($popsoMapppingChart)->filter();

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
              $SlightCorrelation = round(((($correlation[1])/($NoOfPos*$NoOfPsos))*100), 2);
            }
            if (array_key_exists(2,$correlation)) {
              $ModerateCorrelation = round(((($correlation[2])/($NoOfPos*$NoOfPsos))*100), 2);
            }
            if (array_key_exists(3,$correlation)) {
              $HighCorrelation = round(((($correlation[3])/($NoOfPos*$NoOfPsos))*100), 2);
            }
        }
      }

      // return response()->json(['correlation'=>$correlation]);
      return response()->json(['mapping'=>$MapKeyValues,'SlightCorrelation'=>$SlightCorrelation,
      'ModerateCorrelation'=>$ModerateCorrelation, 'HighCorrelation'=>$HighCorrelation,
      'poCount'=>$NoOfPos, 'psoCount'=>$NoOfPsos, 'id' => $PoPsoMappingId, 'chart'=>$popsoMapppingChart]);
    }

    public function editPoPsoMapping(Request $request, $PoPsoMappingId)
    {
      $status="";
      $message="";
      $request->validate([
          'psopos' => 'required',
      ]);
      try {
          if($psopo = Psopo::find($PoPsoMappingId))
          {
              $psopo->mapping = $request->psopos;
              $psopo->save();
          }

          $status = 'success';
          $message = "PO-PSO Mapping Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating PO-PSO Mapping',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update PO-PSO Mapping";

      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function addPeoPsoMapping(Request $request)
    {
      $status = "";
      $message = "";
      $request->validate([
        'school_id' => ['required'],
        'program_id' => ['required'],
        'peopsos' => ['required'],
      ]);

      try {
        Peopso::create([
              'id' => Str::uuid(),
              'school_id' => $request->school_id,
              'program_id' => $request->program_id,
              'mapping' => json_encode($request->peopsos),
              'saved_by' => auth()->user()->id,
        ]);
        $status = 'success';
        $message = "PEO-PSO Mapping Added Successfully";
      } catch (Exception $e) {
        Log::warning('Error Adding PEO-PSO Mapping',$e->getMessage());
        $status = 'error';
        $message = "Unable to PEO-PSO";
      }
      return response()->json(['status' => $status, 'message' => $message]);
    }

    public function getPeoPsoMapping(Request $request, $program_id, $school_id)
    {
      $peopsoMapppingChart = array();
      $MapKey = array();
      $MapKeyValues = array();
      $NoOfPeos = Peo::where('school_id', $school_id)->count();
      $NoOfPsos = Pso::where('program_id', $program_id)->count();
      $PeoPsoMapping = DB::select('SELECT mapping FROM peopsos WHERE program_id = :id', ['id' => $program_id]);
      $PeoPsoMappingId = DB::select('SELECT id FROM peopsos WHERE program_id = :id', ['id' => $program_id]);
      if ($PeoPsoMapping == null) {
        $PeoPsoMapping = array('');
        return response()->json(['mapping'=>$MapKeyValues, 'peoCount'=>$NoOfPeos, 'psoCount'=>$NoOfPsos]);
      }
      $mapping = $PeoPsoMapping[0];

      foreach ($mapping as $key => $value) {
        $MapKey =  $value;
      }
      $MapKey = json_decode($MapKey, true);
      for ($i = 0; $i < sizeof($MapKey); $i++) {
        $MapKeyValues[$i] = $MapKey[$i];
      }
      $labelData = "peopso";
      $labelChart = array("PSO1", "PSO2", "PSO3", "PSO4", "PSO5", "PSO6", "PSO7",
      "PSO8", "PSO9", "PSO10", "PSO11", "PSO12", "PSO13", "PSO14", "PSO15");

      $mappingChart = array();

      $SlightCorrelation = 0;
      $ModerateCorrelation = 0;
      $HighCorrelation = 0;

      if (isset($MapKeyValues[$NoOfPeos-1])) {

        for ($i=0; $i<$NoOfPeos; $i++) {
          // array_push($mappingChart,$labelChart[$i]);
          for ($j=1; $j<=$NoOfPsos; $j++) {
            $mappingChart[] = $MapKeyValues[$i][$labelData.$j];
          }
        }
        $requiredPSOlabels = array();
        for ($i=0; $i < $NoOfPsos; $i++) {
          $requiredPSOlabels[] = $labelChart[$i];
        }
        $psopeoMappingChart = array_chunk($mappingChart, $NoOfPsos);

        array_unshift($psopeoMappingChart, $requiredPSOlabels);

        for ($i=0; $i < $NoOfPsos; $i++) {
          $peopsoMapppingChart[] = array_column($psopeoMappingChart, $i);
        }

        $peopsoMapppingChart = collect($peopsoMapppingChart)->filter();


        $checkEmptyPso = 0;
        for ($i=0; $i < sizeof($mappingChart); $i++) {
            if (null == $mappingChart[$i]) {
              $checkEmptyPso = 1;
            }
        }

        if ($checkEmptyPso == 0) {
          // Counting Corelation
          $correlation = array_count_values($mappingChart);

            if (array_key_exists(1,$correlation)) {
              $SlightCorrelation = round(((($correlation[1])/($NoOfPeos*$NoOfPsos))*100), 2);
            }
            if (array_key_exists(2,$correlation)) {
              $ModerateCorrelation = round(((($correlation[2])/($NoOfPeos*$NoOfPsos))*100), 2);
            }
            if (array_key_exists(3,$correlation)) {
              $HighCorrelation = round(((($correlation[3])/($NoOfPeos*$NoOfPsos))*100), 2);
            }
        }
      }

      // return response()->json(['correlation'=>$correlation]);
      return response()->json(['mapping'=>$MapKeyValues,'SlightCorrelation'=>$SlightCorrelation,
      'ModerateCorrelation'=>$ModerateCorrelation, 'HighCorrelation'=>$HighCorrelation,'peoCount'=>$NoOfPeos,
      'psoCount'=>$NoOfPsos, 'id' => $PeoPsoMappingId, 'chart'=>$peopsoMapppingChart]);
    }

    public function editPeoPsoMapping(Request $request, $PeoPsoMappingId)
    {
      $status="";
      $message="";
      $request->validate([
          'peopsos' => 'required',
      ]);
      try {
          if($peopso = Peopso::find($PeoPsoMappingId))
          {
              $peopso->mapping = $request->peopsos;
              $peopso->save();
          }

          $status = 'success';
          $message = "PEO-PSO Mapping Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating PEO-PSO Mapping',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update PEO-PSO Mapping";
      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

}
