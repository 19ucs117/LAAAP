<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peopo extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';


    protected $casts = [
      'peopos' => 'array',
      'MapKeyValues'=> 'array'
    ];

    public function setPropertiesAttribute($value){
      $peopos = [];

	    foreach ($value as $array_item) {
	        if (!is_null($array_item['key'])) {
	            $properties[] = $array_item;
	        }
	    }

	    $this->attributes['peopos'] = json_encode($peopos);
    }

    protected $fillable = [
      'id',
      'school_id',
      'mapping',
      'saved_by',
      'submit',
    ];
}
