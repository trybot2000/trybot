<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *      ESPN Fantasy Football transaction details
 */
class PlayerProTeamChange extends Model
{
    // Use revisionable trait to track model changes
    use \Venturecraft\Revisionable\RevisionableTrait;

    protected $revisionCreationsEnabled = true;
    protected $keepRevisionOf           = [];
    protected $dontKeepRevisionOf       = [];

    protected $connection = 'fantasyfootball';

    protected $table = 'playerProTeamChanges';

    protected $fillable = ['playerId', 'currentProTeamId', 'priorProTeamId', 'proTeamMoveType', 'currentRosterStatus', 'priorRosterStatus', 'IsProcessed'];

    protected $primaryKey = 'primary';
    public $incrementing  = true;

    public $timestamps = false;

    public function player()
    {
        return $this->hasOne('\App\Models\EspnAllPlayers', 'playerId', 'playerId');
    }

    public static function boot()
    {
        parent::boot();
    }
}
