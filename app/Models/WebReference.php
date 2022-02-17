<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebReference extends Model
{
    use HasFactory;

    protected $table = 'WebReferences';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
      'id',
      'department_id',
      'course_id',
      'WebReferences',
      'saved_by',
      'submit',
    ];
}
