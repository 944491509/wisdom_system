<?php


namespace App\Models\Students;

use Illuminate\Database\Eloquent\Model;

class StudentAdditionInformation extends Model
{
    protected $fillable = ['user_id', 'reward', 'punishment', 'people', 'mobile', 'address'];
}
