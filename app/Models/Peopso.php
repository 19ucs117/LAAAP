<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peopso extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
      'peopsos' => 'array',
    ];

    public function setPropertiesAttribute($value){
      $peopso = [];

	    foreach ($value as $array_item) {
	        if (!is_null($array_item['key'])) {
	            $properties[] = $array_item;
	        }
	    }

	    $this->attributes['peopsos'] = json_encode($peopso);
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
