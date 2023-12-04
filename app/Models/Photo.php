<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    function user(){
        return $this->belongs(User::class);
    }

    function property(){
        return $this->belongs(Property::class);
    }
}
