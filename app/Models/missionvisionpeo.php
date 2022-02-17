<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class missionvisionpeo extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
      'missionvisionpeos' => 'array',
    ];

    public function setPropertiesAttribute($value){
      $missionvisionpos = [];

	    foreach ($value as $array_item) {
	        if (!is_null($array_item['key'])) {
	            $properties[] = $array_item;
	        }
	    }

	    $this->attributes['missionvisionpeos'] = json_encode($missionvisionpos);
    }

    protected $fillable = [
      'id',
      'school_id',
      'mapping',
      'saved_by',
      'submit',
    ];
}
