<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class student_detail extends Model
{
  use HasFactory;
  protected $primaryKey = 'id';
  public $incrementing = false;
  protected $keyType = 'string';
  protected $table = 'student_details';
  protected $fillable = [
    'id',
    'department_id',
    'program_id',
    'batch_id',
    'departmentNumber',
    'name',
    'section',
  ];
}
