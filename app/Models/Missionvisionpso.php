<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Missionvisionpso extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
      'missionvisionpsos' => 'array',
    ];

    public function setPropertiesAttribute($value){
      $missionvisionpsos = [];
	    foreach ($value as $array_item) {
	        if (!is_null($array_item['key'])) {
	            $properties[] = $array_item;
	        }
	    }
	    $this->attributes['missionvisionpsos'] = json_encode($missionvisionpsos);
    }

    protected $fillable = [
      'id',
      'department_id',
      'program_id',
      'mapping',
      'saved_by',
      'submit',
    ];
}
