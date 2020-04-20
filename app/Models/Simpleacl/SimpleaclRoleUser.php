<?php

namespace App\Models\Simpleacl;

use Illuminate\Database\Eloquent\Model;

class SimpleaclRoleUser extends Model
{
    //
    public $table = 'simpleacl_role_users';
    protected $fillable = [
        'simpleacl_role_id','user_id'
    ];
}
