<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dynamicLessonPlan extends Model
{
  use HasFactory;

  protected $primaryKey = 'id';
  public $incrementing = false;
  protected $keyType = 'string';
  protected $table = 'dynamic_lesson_plans';

  protected $fillable = [
    'id',
    'department_id',
    'course_id',
    'unit',
    'content',
    'teachingHours',
    'cognitiveLevel',
    'cos',
    'coAttainmentThreshold',
    'instructionalMethodologies',
    'directAssessmentMethods',
    'saved_by',
    'submit'
  ];
}
