<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *      ESPN Fantasy Football matchup info
 */
class Matchup extends Model
{
    // Use revisionable trait to track model changes
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \App\Traits\FormatDates;

    protected $revisionCreationsEnabled = true;
    protected $keepRevisionOf           = [];
    protected $dontKeepRevisionOf       = [];

    protected $connection = 'fantasyfootball';

    protected $table = 'matchups';

    protected $fillable = ['leagueId', 'matchupPeriodId', 'isBye', 'homeTeamId', 'homeScore', 'homeGamesInProgress', 'homeTopScorerId', 'homeTopScorerScore', 'homeGamesYetToPlay', 'homeMinutesRemaining', 'homeIsFinal', 'homeProjectedPoints', 'awayTeamId', 'awayScore', 'awayGamesInProgress', 'awayTopScorerId', 'awayTopScorerScore', 'awayGamesYetToPlay', 'awayMinutesRemaining', 'awayIsFinal', 'awayProjectedPoints', 'winner', 'homePointsDifferential', 'awayPointsDifferential', 'hash'];

    protected $primaryKey = 'primary';
    public $incrementing  = true;

    public $timestamps = false;

    public function setHomeScoreAttribute($value)
    {
        $this->attributes['homeScore'] = max($value, 0);
    }

    public function setAwayScoreAttribute($value)
    {
        $this->attributes['awayScore'] = max($value, 0);
    }

    public function setHomeTopScorerScoreAttribute($value)
    {
        $this->attributes['homeTopScorerScore'] = max($value, 0);
    }

    public function setAwayTopScorerScoreAttribute($value)
    {
        $this->attributes['awayTopScorerScore'] = max($value, 0);
    }

    public function homeTeam()
    {
        return $this->hasOne('\App\Models\Team', 'teamId', 'homeTeamId');
    }

    public function awayTeam()
    {
        return $this->hasOne('\App\Models\Team', 'teamId', 'awayTeamId');
    }

    public static function boot()
    {
        parent::boot();
    }
}
