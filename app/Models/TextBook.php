<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextBook extends Model
{
    use HasFactory;

    protected $table = 'TextBooks';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
      'id',
      'department_id',
      'course_id',
      'TextBooks',
      'saved_by',
      'submit',
    ];
}
