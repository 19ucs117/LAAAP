<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment_peopso extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = "assessment_peopsos";

    protected $cast = [
        'peopso' => 'array',
        'direct_attainment' => 'array',
      ];
  
      protected $fillable = [
        'id',
        'academic_year',
        'school_id',
        'department_id',
        'program_id',
        'direct_attainment',
        'peopso'
      ];
}
