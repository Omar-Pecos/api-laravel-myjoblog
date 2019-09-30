<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActiveJourney extends Model
{
	protected $table = 'active_journeys';

   public function user()
    {
        return $this->belongsTo('App\User');
    }
}
