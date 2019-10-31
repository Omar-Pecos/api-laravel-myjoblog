<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','surname','number', 'email','dni','role','active', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Mutators 
    public function setNameAttribute($valor){
        $this->attributes['name'] = strtolower($valor);
    }
    public function setSurnameAttribute($valor){
        $this->attributes['surname'] = strtolower($valor);
    }
    public function setEmailAttribute($valor){
        $this->attributes['email'] = strtolower($valor);
    }

    // Accessors
    public function getNameAttribute($valor){
      
       return ucwords($valor);
    }
    public function getSurnameAttribute($valor){
      
       return ucwords($valor);
    }



     public function active_journey()
    {
        return $this->hasOne('App\ActiveJourney');
    }

    public function journeys(){
        return $this->hasMany('App\Journey');
    }

    public function exports(){
        return $this->hasMany('App\Export');
    }

}
