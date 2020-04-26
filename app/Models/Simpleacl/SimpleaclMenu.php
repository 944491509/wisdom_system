<?php

namespace App\Models\Simpleacl;

use Illuminate\Database\Eloquent\Model;

class SimpleaclMenu extends Model
{
    //
    public $table = 'simpleacl_menus';
    protected $fillable = [
        'name','type', 'parent_id', 'sort', 'icon', 'href'
    ];

    public function children(){
        return $this->hasMany(SimpleaclMenu::class, 'parent_id');
    }

    public function permissions() {
        return $this->hasMany(SimpleaclPermission::class, 'simpleacl_menu_id');
    }
}
