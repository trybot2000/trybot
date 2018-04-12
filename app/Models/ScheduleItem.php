<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *      ESPN Fantasy Football schedule item (i.e. matchup)
 */
class ScheduleItem extends Model
{
    // Use revisionable trait to track model changes
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionCreationsEnabled = true;
    protected $keepRevisionOf           = [];
    protected $dontKeepRevisionOf       = [];

    protected $connection = 'fantasyfootball';

    protected $table = 'scheduleItems';

    protected $fillable = ['leagueId', 'teamsHash', 'matchupTypeId', 'matchupPeriodId', 'isBye', 'homeTeamId', 'homeTeamScores', 'homeTeamAdjustment', 'awayTeamId', 'awayTeamScores', 'awayTeamAdjustment', 'outcome'];

    protected $primaryKey = 'leagueId';

    public $timestamps = false;

    public $incrementing = false;

    public function setIsByeAttribute($value)
    {
        $this->attributes['isBye'] = (int) $value;
    }

    public static function boot()
    {
        parent::boot();
    }
}
