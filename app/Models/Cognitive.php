<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cognitive extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'cm_id_fk',
        'cognitive_level',
        'max_mark',
        'scored_mark',
    ];
}
