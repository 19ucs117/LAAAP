<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment_copso extends Model
{
    use HasFactory;

    protected $table = 'assessment_copsos';

    protected $cast = [
      'psocos' => 'array',
      'direct_assessment' => 'array',
      'indirect_assessment_upload' => 'array',
      'indirectAssessment' => 'array',
      'consolidate_copso' => 'array'
    ];

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
      'id',
      'co_id',
      'indirect_assessment',
      'direct_attainment',
      'copso',
      'indirect_attainment',
      'consolidated_copso',
      'copso_avarage',
      'consolidate',
      'feed_back'
    ];
    

    







}
