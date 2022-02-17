<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseObjective extends Model
{
    use HasFactory;

    protected $table = 'CourseObjectives';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
      'id',
      'department_id',
      'course_id',
      'courseObjectives',
      'saved_by',
      'submit',
    ];
}
