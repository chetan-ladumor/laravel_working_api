<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable,SoftDeletes,HasApiTokens;
   
    protected $dates=['deleted_at'];

    const VERIFIED_USER='1';
    const UNVERIFIED_USER='0';

    const ADMIN_USER='true';
    const REGULAR_USER='false';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','verified','verification_token','admin'
    ];

    protected $table='users'; // buyers and seller are going to inherit this value.

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
         'remember_token','verification_token',
    ];

    //mutator

    public function setNameAttribute($name)
    {
        $this->attributes['name']=strtolower($name);
    }

    //accessors

    public function getNameAttribute($name)
    {
        return ucwords($name);
    }

     public function setEameAttribute($email)
    {
        $this->attributes['email']=strtolower($email);
    }

    public function isAmdin()
    {
        return $this->verified == User::VERIFIED_USER;
    }

    public function isverified()
    {
        return $this->admin == User::ADMIN_USER;
    }

    public static function generateVerificationCode()
    {
        return str_random(40);
    }

}
