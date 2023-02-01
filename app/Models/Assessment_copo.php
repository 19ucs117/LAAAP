<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment_copo extends Model
{
    use HasFactory;
    protected $table = 'assessment_copos';

    protected $cast = [
      'pocos' => 'array',
      'direct_assessment' => 'array',
      'indirect_assessment_upload' => 'array',
      'indirectAssessment' => 'array',
      'consolidate_copo' => 'array',
    ];
    

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'co_id',
        'indirect_assessment',
        'direct_attainment',
        'copo',
        'indirect_attainment',
        'consolidated_copo',
        'copo_avarage',
        'consolidate',
        'feed_back'
    ];
}
