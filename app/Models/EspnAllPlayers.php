<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class EspnAllPlayers extends Model
{
    // Use revisionable trait to track model changes
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionCreationsEnabled = true;
    protected $keepRevisionOf           = [];
    protected $dontKeepRevisionOf       = [];

    protected $connection = 'fantasyfootball';

    protected $table = 'espnAllPlayers';

    protected $fillable = ['playerId','firstName','lastName','proTeamId','team','percentOwned','percentStarted','latestNewsTenWords','latestNewsEvaluation','totalPoints','positionRank','position','rosterStatus','eligibleSlotCategoryIds','currentPeriodProjectedStats','proGameIds','previousSeasonRealStats','currentSeasonRealStats','currentPeriodRealStats','healthStatus','defaultPositionId','universeId','opponentProTeamId','pvoRank','droppable'];

    public $timestamps = false;
    protected $primaryKey = 'primary';
    public $incrementing  = true;

    public function nflTeam()
    {
        return $this->hasOne('\App\Models\NflTeam', 'proTeamId', 'proTeamId');
    }

    public function roster()
    {
        return $this->belongsTo('\App\Models\Roster', 'playerId', 'playerId');
    }

    public function rosterWithTeam()
    {
        return $this->belongsTo('\App\Models\Roster', 'playerId', 'playerId')->with('team');
    }

    public static function boot()
    {
        parent::boot();
    }
}
