<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;


use App\Models\vissionMission;

class VissionMissionController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(['auth', 'superadmin']);
    }

    public function addVissionMission(Request $request)
    {
        $request->validate([
          'vision' => ['required'],
          'mission_one' => ['required'],
          'mission_two' => ['required'],
          'mission_three' => ['required'],
          'mission_four' => ['required'],
        ]);
        try {
            vissionMission::create([
                'id' => Str::uuid(),
                'vision' => $request->vision,
                'mission_one' => $request->mission_one,
                'mission_two' => $request->mission_two,
                'mission_three' => $request->mission_three,
                'mission_four' => $request->mission_four,
            ]);
            return response()->json(['status' => 'Success', 'msg' => 'Successfully Created']);
        } catch (Exception $e) {
            return response()->json(['status' => 'Failed', 'msg' => $e->getMessage()]);
        }
    }



    public function editVissionMission(Request $request, $id)
    {
      $status="";
      $message="";
      $request->validate([
        'vision' => ['required'],
        'mission_one' => ['required'],
        'mission_two' => ['required'],
        'mission_three' => ['required'],
        'mission_four' => ['required'],
      ]);
      try {
          if ($vissionMission = vissionMission::find($id)) {
            $vissionMission->vision = $request->vision;
            $vissionMission->mission_one = $request->mission_one;
            $vissionMission->mission_two = $request->mission_two;
            $vissionMission->mission_three = $request->mission_three;
            $vissionMission->mission_four = $request->mission_four;
            $vissionMission->save();
          }
          $status = 'success';
          $message = "VissionMission Updated Successfully";
      } catch (Exception $e) {
          Log::warning('Error Updating VissionMission',$e->getMessage());
          $status = 'error';
          $message = "Unable to Update VissionMission";

      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

    public function deleteVissionMission(Request $request, $id)
    {
      $status="";
      $message="";
      try {
          if ($vissionMission = vissionMission::find($id)) {
            $vissionMission -> delete();
          }
          $status = 'success';
          $message = "VissionMission Deleted Successfully";
      } catch (Exception $e) {
          Log::warning('Error Deleting VissionMission',$e->getMessage());
          $status = 'error';
          $message = "Unable to Delete VissionMission";

      }

      return response()->json(['status' => $status,'message'=>$message]);
    }

}
