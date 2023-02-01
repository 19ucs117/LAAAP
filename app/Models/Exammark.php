<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exammark extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'exammarks';

    protected $casts = [
      'markEntry' => 'array'
    ];

    protected $fillable = [
      'id',
      'department_id',
      'batch_id',
      'course_id',
      'section',
      'assessment',
      'saved_by',
    ];
}
