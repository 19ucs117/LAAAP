<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Justificationpsopeo extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';


    protected $casts = [
      'justification' => 'array'
    ];

    protected $fillable = [
      'id',
      'program_id',
      'mappingJustification',
      'saved_by',
      'submit',
    ];
}
