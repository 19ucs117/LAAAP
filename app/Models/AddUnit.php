<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddUnit extends Model
{
    use HasFactory;

    protected $table = 'AddUnits';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
      'id',
      'department_id',
      'course_id',
      'units',
      'content',
      'hours',
      'cos',
      'cogLevel',
      'saved_by',
      'submit',
    ];
}
