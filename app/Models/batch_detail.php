<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class batch_detail extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'batch_details';

    protected $fillable = [
        'id',
        'department_id',
        'program_id',
        'batchNo',
        'NoSections'
    ];
}
