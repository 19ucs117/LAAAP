<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Psopo extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
      'psopos' => 'array',
    ];

    public function setPropertiesAttribute($value){
      $psopo = [];

	    foreach ($value as $array_item) {
	        if (!is_null($array_item['key'])) {
	            $properties[] = $array_item;
	        }
	    }

	    $this->attributes['psopos'] = json_encode($psopo);
    }

    protected $fillable = [
      'id',
      'school_id',
      'program_id',
      'mapping',
      'saved_by',
      'submit',
    ];
}
