<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Co extends Model
{
  use HasFactory;
  protected $primaryKey = 'id';
  public $incrementing = false;
  protected $keyType = 'string';
  protected $fillable = [

      'id',
      'department_id',
      'course_id',
      'labelNo',
      'description',
      'cogLevel',
      'saved_by',
      'submit',

  ];
}
