<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TodoList extends Model
{

    use SoftDeletes;
    protected $fillable = ['name', 'user_id'];
}
