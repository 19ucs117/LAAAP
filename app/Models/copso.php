<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class copso extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
      'copsos' => 'array',
    ];

    public function setPropertiesAttribute($value){
      $copsos = [];

	    foreach ($value as $array_item) {
	        if (!is_null($array_item['key'])) {
	            $properties[] = $array_item;
	        }
	    }

	    $this->attributes['copsos'] = json_encode($copsos);
    }

    protected $fillable = [
      'id',
      'program_id',
      'course_id',
      'mapping',
      'saved_by',
      'submit',
    ];
}
