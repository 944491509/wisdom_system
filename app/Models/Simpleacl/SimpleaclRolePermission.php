<?php

namespace App\Models\Simpleacl;

use Illuminate\Database\Eloquent\Model;

class SimpleaclRolePermission extends Model
{
    //
    public $table = 'simpleacl_role_permissions';
    protected $fillable = [
        'simpleacl_role_id','simpleacl_permission_id'
    ];
    public function permissions()
    {
        return $this->belongsTo(SimpleaclPermission::class, 'simpleacl_permission_id');
    }
}
