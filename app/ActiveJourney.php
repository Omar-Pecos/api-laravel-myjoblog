<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActiveJourney extends Model
{
	protected $table = 'active_journeys';
	 protected $fillable = [
        'paused'
    ];

   public function user()
    {
        return $this->belongsTo('App\User');
    }
}
