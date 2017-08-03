<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Guess extends Model
{
    public $timestamps = true;
    
    protected $table   = 'guesses';

    protected $fillable = ['name', 'ip', 'guess'];

}
