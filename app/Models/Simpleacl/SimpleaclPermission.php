<?php

namespace App\Models\Simpleacl;

use Illuminate\Database\Eloquent\Model;

class SimpleaclPermission extends Model
{
    //
    public $table = 'simpleacl_permissions';
    protected $fillable = [
        'name','router', 'simpleacl_menu_id'
    ];
}
