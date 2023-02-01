<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Studentmark extends Model
{
    use HasFactory;

    protected $table = 'studentmarks';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'student' => 'array',
        'coDirectMarks' => 'array',
        'indirect_student' => 'array',
        'consolidatedCO' => 'array',
        'indirectMarksFeedBack' => 'array',
        'co_consolidated_value' => 'array',

    ];
    protected $fillable = [
        'id',
        'department_name',
        'program_name',
        'regulation',
        'course_code',
        'course_title',
        'staff_code',
        'section',
        'staff_name',
        'direct_attainment',
        'indirect_attainment',
        'feed_back',
        'co',
        'consolidated_co',
        'co_avarage'
    ];
}
