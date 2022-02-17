<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Copeo extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
      'copeos' => 'array',
    ];

    protected $fillable = [
      'id',
      'school_id',
      'course_id',
      'mapping',
      'saved_by',
      'submit',
    ];
}
