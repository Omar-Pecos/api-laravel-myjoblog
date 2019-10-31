<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Export extends Model
{
     protected $table = 'exports';

   public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    //accesor del datetime
    public function getDatetimeAttribute($valor){

    	$datetime = explode(" ",$valor);
        
         $date =  explode("-", $datetime[0]);
        $string_date = $date[2].'/'.$date[1].'/'.$date[0];
        
       return $string_date." ".$datetime[1];
    }
}
