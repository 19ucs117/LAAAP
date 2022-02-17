<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class course_code extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'course_code',
        'department_id',
        'program_id',
        'course_title',
        'credits',
        'hours',
        'category',
        'semester',
        'course_assigned_toStaff',
        'created_by',
        'updated_by',
    ];
}
