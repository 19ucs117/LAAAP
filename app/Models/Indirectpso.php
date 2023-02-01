<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indirectpso extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'indirectpsos';

    protected $casts = [
      'indirect_assessment_mark' => 'array',
      'indirect_assessment_mark_100' => 'array',
      'indirect_assessment_mark_10' => 'array'
    ];

    protected $fillable = [
      'id',
      'regulation',
      'department_name',
      'program_name',
      'staff_id',
      'indirect_assessment',
      'indirect_assessment_100_percentage',
      'indirect_assessment_10_percentage'
    ];
}