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

    // Accessors
    public function getTimeAttribute($valor){
    	
        $num = $valor/60/60;
       return round($num, 2);

    }

    public function getDateAttribute($valor){
        
         $date =  explode("-", $valor);
        $string_date = $date[2].'/'.$date[1].'/'.$date[0];
        
       return $string_date;
    }

   
}
