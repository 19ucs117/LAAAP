<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenceBook extends Model
{
    use HasFactory;

    protected $table = 'ReferenceBooks';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
      'id',
      'department_id',
      'course_id',
      'ReferenceBooks',
      'saved_by',
      'submit',
    ];
}
