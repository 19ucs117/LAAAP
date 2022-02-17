<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseOverview extends Model
{
    use HasFactory;

    protected $table='CourseOverviews';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
      'id',
      'department_id',
      'course_id',
      'courseOverview',
      'saved_by',
      'submit',
    ];
}
