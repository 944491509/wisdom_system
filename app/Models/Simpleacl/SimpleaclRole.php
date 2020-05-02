<?php

namespace App\Models\Simpleacl;

use App\User;
use Illuminate\Database\Eloquent\Model;

class SimpleaclRole extends Model
{
    //
    public $table = 'simpleacl_roles';
    protected $fillable = [
        'name','type','description'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'simpleacl_role_users', 'simpleacl_role_id', 'user_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(SimpleaclPermission::class, 'simpleacl_role_permissions', 'simpleacl_role_id', 'simpleacl_permission_id');
    }
}
