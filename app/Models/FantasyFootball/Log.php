<?php

namespace App\Models\FantasyFootball;

use Illuminate\Database\Eloquent\Model;

/**
 *      Log an action taken and the result
 */
class Log extends Model
{
    protected $connection = 'fantasyfootball';

    protected $table = 'log';

    protected $fillable = ['result', 'leagueId', 'error', 'meta', 'headers', 'playerStatusChanges'];

    protected $primaryKey = 'primary';

    public $incrementing = true;

    public $timestamps = false;

}
