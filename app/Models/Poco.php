<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poco extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
      'pocos' => 'array',
    ];

    public function setPropertiesAttribute($value){
      $poco = [];

	    foreach ($value as $array_item) {
	        if (!is_null($array_item['key'])) {
	            $properties[] = $array_item;
	        }
	    }

	    $this->attributes['pocos'] = json_encode($poco);
    }

    protected $fillable = [
      'id',
      'school_id',
      'course_id',
      'mapping',
      'saved_by',
      'submit',
    ];
}
