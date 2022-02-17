<?php

namespace App\Http\Controllers;

use App\Models\Cognitive;
use App\Models\CognitiveMap;
use App\Models\CourseOutcome;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CourseOutcomeController extends Controller
{
    //
    public function addMark(Request $request)
    {
        $cid = Str::uuid();


        $cmp_id = Str::uuid();

        CognitiveMap::create([
            'id' => $cmp_id,
            'coq_id' => $cid,
            'student_id' => $request->student_id,
        ]);

        foreach ($request->cognitive as $val) {
            Cognitive::create([
                'id' => Str::uuid(),
                'cm_id_fk' => $cmp_id,
                'cognitive_level' => $val['cognetive_level'],
                'max_mark' => $val['max_mark'],
                'scored_mark' => $val['scored_mark'],
            ]);

        }
        return response()->json(['Success']);
    }
}
