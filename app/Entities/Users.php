<?php


namespace App\Entities;


use Illuminate\Database\Eloquent\Model;


class Users extends Model
{
    protected $table = 'users';
    public $incrementing = false;
}
