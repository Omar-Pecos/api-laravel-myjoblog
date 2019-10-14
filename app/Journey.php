<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Journey extends Model
{
	 protected $table = 'journeys';

   public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
