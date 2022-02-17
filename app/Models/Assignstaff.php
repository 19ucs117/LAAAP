<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignstaff extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'assignstaffs';

    protected $fillable = [
        'id',
        'department_id',
        'program_id',
        'course_id',
        'batch_id',
        'section',
        'user_id',
        'assigned_by',
    ];
}
